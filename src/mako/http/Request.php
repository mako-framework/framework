<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http;

use mako\http\request\Body;
use mako\http\request\Cookies;
use mako\http\request\Files;
use mako\http\request\Headers;
use mako\http\request\Parameters;
use mako\http\request\Server;
use mako\http\routing\Route;
use mako\security\Signer;
use mako\utility\Arr;
use mako\utility\ip\IP;

use function array_map;
use function array_reverse;
use function basename;
use function current;
use function explode;
use function file_get_contents;
use function filter_var;
use function fopen;
use function in_array;
use function ltrim;
use function mb_strlen;
use function mb_substr;
use function parse_url;
use function pathinfo;
use function rawurldecode;
use function rtrim;
use function str_replace;
use function stripos;
use function strlen;
use function strpos;
use function strtok;
use function strtoupper;

/**
 * Executes requets.
 */
class Request
{
	/**
	 * Remote address fallback.
	 *
	 * @var string
	 */
	public const REMOTE_ADDRESS_FALLBACK = '127.0.0.1';

	/**
	 * Script name.
	 *
	 * @var string
	 */
	protected $scriptName;

	/**
	 * Get data.
	 *
	 * @var \mako\http\request\Parameters
	 */
	protected $query;

	/**
	 * Post data.
	 *
	 * @var \mako\http\request\Parameters
	 */
	protected $post;

	/**
	 * Cookie data.
	 *
	 * @var \mako\http\request\Cookies
	 */
	protected $cookies;

	/**
	 * File data.
	 *
	 * @var \mako\http\request\Files
	 */
	protected $files;

	/**
	 * Server info.
	 *
	 * @var \mako\http\request\Server
	 */
	protected $server;

	/**
	 * Request headers.
	 *
	 * @var \mako\http\request\Headers
	 */
	protected $headers;

	/**
	 * Raw request body.
	 *
	 * @var string
	 */
	protected $rawBody;

	/**
	 * Parsed request body.
	 *
	 * @var \mako\http\request\Body
	 */
	protected $parsedBody;

	/**
	 * Content type.
	 *
	 * @var string
	 */
	protected $contentType;

	/**
	 * Array of trusted proxy IP addresses.
	 *
	 * @var array
	 */
	protected $trustedProxies = [];

	/**
	 * Ip address of the client that made the request.
	 *
	 * @var string
	 */
	protected $ip;

	/**
	 * Base path of the request.
	 *
	 * @var string
	 */
	protected $basePath;

	/**
	 * Base URL of the request.
	 *
	 * @var string
	 */
	protected $baseURL;

	/**
	 * Holds the request path.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Request language.
	 *
	 * @var array
	 */
	protected $language;

	/**
	 * Request language prefix.
	 *
	 * @var string
	 */
	protected $languagePrefix;

	/**
	 * Which request method was used?
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * The actual request method that was used.
	 *
	 * @var string
	 */
	protected $realMethod;

	/**
	 * Was this request made using HTTPS?
	 *
	 * @var bool
	 */
	protected $isSecure;

	/**
	 * The route that matched the request.
	 *
	 * @var \mako\http\routing\Route
	 */
	protected $route;

	/**
	 * Request attribuntes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Constructor.
	 *
	 * @param array                      $request    Request data and options
	 * @param \mako\security\Signer|null $signer     Signer instance used to validate signed cookies
	 * @param string|null                $scriptName Script name
	 */
	public function __construct(array $request = [], ?Signer $signer = null, ?string $scriptName = null)
	{
		// Collect request data

		$this->query   = new Parameters($request['get'] ?? $_GET);
		$this->post    = new Parameters($request['post'] ?? $_POST);
		$this->cookies = new Cookies($request['cookies'] ?? $_COOKIE, $signer);
		$this->files   = new Files($request['files'] ?? $_FILES);
		$this->server  = new Server($request['server'] ?? $_SERVER);
		$this->headers = new Headers($this->server->getHeaders());
		$this->rawBody = $request['body'] ?? null;

		// Get the script name

		$this->scriptName = $scriptName ?? basename($this->server->get('SCRIPT_FILENAME', ''));

		if($this->scriptName === 'reactor')
		{
			$this->scriptName = 'index.php';
		}

		// Set the request path and method

		$languages = $request['languages'] ?? [];

		$this->path = isset($request['path']) ? $this->stripLocaleSegment($languages, $request['path']) : $this->determinePath($languages);

		$this->method = $request['method'] ?? $this->determineMethod();
	}

	/**
	 * Strips the locale segment from the path.
	 *
	 * @param  array  $languages Locale segments
	 * @param  string $path      Path
	 * @return string
	 */
	protected function stripLocaleSegment(array $languages, string $path): string
	{
		foreach($languages as $key => $language)
		{
			if($path === "/{$key}" || strpos($path, "/{$key}/") === 0)
			{
				$this->language = $language;

				$this->languagePrefix = $key;

				$path = '/' . ltrim(mb_substr($path, (mb_strlen($key) + 1)), '/');

				break;
			}
		}

		return $path;
	}

	/**
	 * Determines the request path.
	 *
	 * @param  array  $languages Locale segments
	 * @return string
	 */
	protected function determinePath(array $languages): string
	{
		$path = '/';

		$server = $this->server->all();

		if(isset($server['PATH_INFO']))
		{
			$path = $server['PATH_INFO'];
		}
		elseif(isset($server['REQUEST_URI']))
		{
			if($parsed = parse_url($server['REQUEST_URI'], PHP_URL_PATH))
			{
				$path = $parsed;

				// Remove base path from the request path

				$basePath = pathinfo($server['SCRIPT_NAME'], PATHINFO_DIRNAME);

				if($basePath !== '/' && stripos($path, $basePath) === 0)
				{
					$path = mb_substr($path, mb_strlen($basePath));
				}

				// Remove "/index.php" from the path

				if(stripos($path, "/{$this->scriptName}") === 0)
				{
					$path = mb_substr($path, (strlen($this->scriptName) + 1));
				}

				$path = rawurldecode($path);
			}
		}

		return $this->stripLocaleSegment($languages, $path);
	}

	/**
	 * Determines the request method.
	 *
	 * @return string
	 */
	protected function determineMethod(): string
	{
		$this->realMethod = $method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));

		if($method === 'POST')
		{
			return strtoupper($this->post->get('REQUEST_METHOD_OVERRIDE', $this->server->get('HTTP_X_HTTP_METHOD_OVERRIDE', 'POST')));
		}

		return $method;
	}

	/**
	 * Returns the content type of the request body.
	 * An empty string will be returned if the header is missing.
	 *
	 * @return string
	 */
	public function getContentType(): string
	{
		if($this->contentType === null)
		{
			$this->contentType = rtrim(strtok((string) $this->headers->get('content-type'), ';'));
		}

		return $this->contentType;
	}

	/**
	 * Returns the base name of the script that handled the request.
	 *
	 * @return string
	 */
	public function getScriptName(): string
	{
		return $this->scriptName;
	}

	/**
	 * Set the route that matched the request.
	 *
	 * @param \mako\http\routing\Route $route Route
	 */
	public function setRoute(Route $route): void
	{
		$this->route = $route;
	}

	/**
	 * Returns the route that matched the request.
	 *
	 * @return \mako\http\routing\Route|null
	 */
	public function getRoute(): ?Route
	{
		return $this->route;
	}

	/**
	 * Sets a request attribute.
	 *
	 * @param string $name  Attribute name
	 * @param mixed  $value Attribute value
	 */
	public function setAttribute(string $name, $value): void
	{
		Arr::set($this->attributes, $name, $value);
	}

	/**
	 * Gets a request attribute.
	 *
	 * @param  string $name    Attribute name
	 * @param  mixed  $default Default value
	 * @return mixed
	 */
	public function getAttribute(string $name, $default = null)
	{
		return Arr::get($this->attributes, $name, $default);
	}

	/**
	 * Returns the raw request body.
	 *
	 * @return string
	 */
	public function getRawBody(): string
	{
		if($this->rawBody === null)
		{
			$this->rawBody = file_get_contents('php://input');
		}

		return $this->rawBody;
	}

	/**
	 * Returns the raw request body as a stream.
	 *
	 * @return resource
	 */
	public function getRawBodyAsStream()
	{
		return fopen('php://input', 'r');
	}

	/**
	 * Returns the query string.
	 *
	 * @return \mako\http\request\Parameters
	 */
	public function getQuery(): Parameters
	{
		return $this->query;
	}

	/**
	 * Returns the post data.
	 *
	 * @return \mako\http\request\Parameters
	 */
	public function getPost(): Parameters
	{
		return $this->post;
	}

	/**
	 * Returns the cookies.
	 *
	 * @return \mako\http\request\Cookies
	 */
	public function getCookies(): Cookies
	{
		return $this->cookies;
	}

	/**
	 * Returns the files.
	 *
	 * @return \mako\http\request\Files
	 */
	public function getFiles(): Files
	{
		return $this->files;
	}

	/**
	 * Returns the files.
	 *
	 * @return \mako\http\request\Server
	 */
	public function getServer(): Server
	{
		return $this->server;
	}

	/**
	 * Returns the files.
	 *
	 * @return \mako\http\request\Headers
	 */
	public function getHeaders(): Headers
	{
		return $this->headers;
	}

	/**
	 * Returns the parsed request body.
	 *
	 * @return \mako\http\request\Body
	 */
	public function getBody(): Body
	{
		if($this->parsedBody === null)
		{
			$this->parsedBody = new Body($this->getRawBody(), $this->getContentType());
		}

		return $this->parsedBody;
	}

	/**
	 * Returns TRUE if the request has form data and FALSE if not.
	 *
	 * @return bool
	 */
	protected function hasFormData(): bool
	{
		$contentType = $this->getContentType();

		if($contentType === 'application/x-www-form-urlencoded' || $contentType === 'multipart/form-data')
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns the data of the current request method.
	 *
	 * @return \mako\http\request\Parameters
	 */
	public function getData(): Parameters
	{
		if($this->realMethod === 'GET')
		{
			return $this->getQuery();
		}
		elseif($this->realMethod === 'POST' && $this->hasFormData())
		{
			return $this->getPost();
		}

		return $this->getBody();
	}

	/**
	 * Set the trusted proxies.
	 *
	 * @param array $trustedProxies Array of trusted proxy IP addresses
	 */
	public function setTrustedProxies(array $trustedProxies): void
	{
		$this->trustedProxies = $trustedProxies;
	}

	/**
	 * Is this IP a trusted proxy?
	 *
	 * @param  string $ip IP address
	 * @return bool
	 */
	protected function isTrustedProxy(string $ip): bool
	{
		foreach($this->trustedProxies as $trustedProxy)
		{
			if(IP::inRange($ip, $trustedProxy))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the ip of the client that made the request.
	 *
	 * @return string
	 */
	public function getIp(): string
	{
		if($this->ip === null)
		{
			$ip = $this->server->get('REMOTE_ADDR');

			if($ip !== null && $this->isTrustedProxy($ip))
			{
				$ips = $this->server->get('HTTP_X_FORWARDED_FOR');

				if(!empty($ips))
				{
					$ips = array_reverse(array_map('trim', explode(',', $ips)));

					foreach($ips as $key => $value)
					{
						if($this->isTrustedProxy($value) === false)
						{
							break;
						}

						unset($ips[$key]);
					}

					$ip = current($ips);
				}
			}

			$this->ip = (filter_var($ip, FILTER_VALIDATE_IP) !== false) ? $ip : static::REMOTE_ADDRESS_FALLBACK;
		}

		return $this->ip;
	}

	/**
	 * Returns TRUE if the request was made using Ajax and FALSE if not.
	 *
	 * @return bool
	 */
	public function isAjax(): bool
	{
		return $this->server->get('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
	}

	/**
	 * Returns TRUE if the request was made using HTTPS and FALSE if not.
	 *
	 * @return bool
	 */
	public function isSecure(): bool
	{
		if($this->isSecure === null)
		{
			if($this->isTrustedProxy($this->server->get('REMOTE_ADDR', static::REMOTE_ADDRESS_FALLBACK)) && $this->server->get('HTTP_X_FORWARDED_PROTO') === 'https')
			{
				return $this->isSecure = true;
			}

			return $this->isSecure = filter_var($this->server->get('HTTPS', false), FILTER_VALIDATE_BOOLEAN);
		}

		return $this->isSecure;
	}

	/**
	 * Returns TRUE if the request method is considered safe and FALSE if not.
	 *
	 * @return bool
	 */
	public function isSafe(): bool
	{
		return in_array($this->method, ['GET', 'HEAD', 'OPTIONS', 'TRACE']);
	}

	/**
	 * Returns TRUE if the request method is considered idempotent and FALSE if not.
	 *
	 * @return bool
	 */
	public function isIdempotent(): bool
	{
		return in_array($this->method, ['DELETE', 'GET', 'HEAD', 'OPTIONS', 'PUT', 'TRACE']);
	}

	/**
	 * Returns TRUE if the request method is considered cacheable and FALSE if not.
	 *
	 * @return bool
	 */
	public function isCacheable(): bool
	{
		return in_array($this->method, ['GET', 'HEAD']);
	}

	/**
	 * Is PHP running as a CGI program?
	 *
	 * @return bool
	 */
	public function isCGI(): bool
	{
		return strpos(PHP_SAPI, 'cgi') !== false;
	}

	/**
	 * Returns the base path of the request.
	 *
	 * @return string
	 */
	public function getBasePath(): string
	{
		if($this->basePath === null)
		{
			$path = $this->server->get('SCRIPT_NAME', '');

			$this->basePath = rtrim(str_replace(basename($path), '', $path), '/');
		}

		return $this->basePath;
	}

	/**
	 * Returns the base url of the request.
	 *
	 * @return string
	 */
	public function getBaseURL(): string
	{
		if($this->baseURL === null)
		{
			// Get the protocol

			$protocol = $this->isSecure() ? 'https://' : 'http://';

			// Get the server name and port

			if(($host = $this->server->get('HTTP_HOST')) === null)
			{
				$host = $this->server->get('SERVER_NAME');

				$port = $this->server->get('SERVER_PORT');

				if($port !== null && $port != 80)
				{
					$host = "{$host}:{$port}";
				}
			}

			// Put them all together along with the base path

			$this->baseURL = "{$protocol}{$host}{$this->getBasePath()}";
		}

		return $this->baseURL;
	}

	/**
	 * Returns the request path.
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * Returns TRUE if the resource was requested with a "clean" URL and FALSE if not.
	 *
	 * @return bool
	 */
	public function isClean(): bool
	{
		return strpos($this->server->get('REQUEST_URI', ''), $this->server->get('SCRIPT_NAME', '')) !== 0;
	}

	/**
	 * Returns the request language.
	 *
	 * @return array|null
	 */
	public function getLanguage(): ?array
	{
		return $this->language;
	}

	/**
	 * Returns the request language prefix.
	 *
	 * @return string|null
	 */
	public function getLanguagePrefix(): ?string
	{
		return $this->languagePrefix;
	}

	/**
	 * Returns the request method that was used.
	 *
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

	/**
	 * Returns the real request method that was used.
	 *
	 * @return string
	 */
	public function getRealMethod(): string
	{
		return $this->realMethod;
	}

	/**
	 * Returns TRUE if the request method has been faked and FALSE if not.
	 *
	 * @return bool
	 */
	public function isFaked(): bool
	{
		return $this->realMethod !== $this->method;
	}

	/**
	 * Returns the basic HTTP authentication username or null.
	 *
	 * @return string|null
	 */
	public function getUsername(): ?string
	{
		return $this->server->get('PHP_AUTH_USER');
	}

	/**
	 * Returns the basic HTTP authentication password or null.
	 *
	 * @return string|null
	 */
	public function getPassword(): ?string
	{
		return $this->server->get('PHP_AUTH_PW');
	}

	/**
	 * Returns the referrer.
	 *
	 * @param  mixed $default Value to return if no referrer is set
	 * @return mixed
	 */
	public function getReferrer($default = null)
	{
		return $this->headers->get('referer', $default); // Referrer should be left misspelled here as it's a part of the HTTP spec
	}
}
