<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http;

use RuntimeException;

use mako\http\UploadedFile;
use mako\http\routing\Route;
use mako\security\Signer;
use mako\utility\Arr;
use mako\utility\ip\IP;

/**
 * Executes requets.
 *
 * @author  Frederic G. Østby
 */
class Request
{
	/**
	 * Get data
	 *
	 * @var array
	 */
	protected $get;

	/**
	 * Post data
	 *
	 * @var array
	 */
	protected $post;

	/**
	 * Cookie data.
	 *
	 * @var array
	 */
	protected $cookies;

	/**
	 * File data.
	 *
	 * @var array
	 */
	protected $files;

	/**
	 * Server info.
	 *
	 * @var array
	 */
	protected $server;

	/**
	 * Raw request body.
	 *
	 * @var string
	 */
	protected $body;

	/**
	 * Signer instance.
	 *
	 * @var \mako\security\Signer
	 */
	protected $signer;

	/**
	 * Parsed request body.
	 *
	 * @var array
	 */
	protected $parsedBody;

	/**
	 * Uploaded files.
	 *
	 * @var array
	 */
	protected $uploadedFiles;

	/**
	 * Request headers.
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Array of acceptable content types.
	 *
	 * @var array
	 */
	protected $acceptableContentTypes = [];

	/**
	 * Array of acceptable languages.
	 *
	 * @var array
	 */
	protected $acceptableLanguages = [];

	/**
	 * Array of acceptable charsets.
	 *
	 * @var array
	 */
	protected $acceptableCharsets = [];

	/**
	 * Array of acceptable encodings.
	 *
	 * @var array
	 */
	protected $acceptableEncodings = [];

	/**
	 * Array of trusted proxy IP addresses.
	 *
	 * @var array
	 */
	protected $trustedProxies;

	/**
	 * Ip address of the client that made the request.
	 *
	 * @var string
	 */
	protected $ip;

	/**
	 * Is this an Ajax request?
	 *
	 * @var bool
	 */
	protected $isAjax;

	/**
	 * Was the request made using HTTPS?
	 *
	 * @var bool
	 */
	protected $isSecure;

	/**
	 * Is PHP running as a CGI program?
	 *
	 * @var bool
	 */
	protected $isCGI;

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
	 * @access  public
	 * @param   array                  $request  Request data and options
	 * @param   \mako\security\Signer  $signer   Signer instance used to validate signed cookies
	 */
	public function __construct(array $request = [], Signer $signer = null)
	{
		// Collect request data

		$this->get     = $request['get'] ?? $_GET;
		$this->post    = $request['post'] ?? $_POST;
		$this->cookies = $request['cookies'] ?? $_COOKIE;
		$this->files   = $request['files'] ?? $_FILES;
		$this->server  = $request['server'] ?? $_SERVER;
		$this->body    = $request['body'] ?? null;

		// Set the Signer instance

		$this->signer = $signer;

		// Collect the request headers

		$this->headers = $this->collectHeaders();

		// Collect request info

		$this->collectRequestInfo();

		// Set the request path and method

		$languages = $request['languages'] ?? [];

		$this->path = $request['path'] ?? $this->determinePath($languages);

		$this->method = $request['method'] ?? $this->determineMethod();
	}

	/**
	 * Strips the locale segment from the path.
	 *
	 * @access  protected
	 * @param   array      $languages  Locale segments
	 * @param   string     $path       Path
	 * @return  string
	 */
	protected function stripLocaleSegment(array $languages, string $path): string
	{
		foreach($languages as $key => $language)
		{
			if($path === '/' . $key || strpos($path, '/' . $key . '/') === 0)
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
	 * @access  protected
	 * @param   array      $languages  Locale segments
	 * @return  string
	 */
	protected function determinePath(array $languages): string
	{
		$path = '/';

		if(isset($this->server['PATH_INFO']))
		{
			$path = $this->server['PATH_INFO'];
		}
		elseif(isset($this->server['REQUEST_URI']))
		{
			if($path = parse_url($this->server['REQUEST_URI'], PHP_URL_PATH))
			{
				// Remove base path from the request path

				$basePath = pathinfo($this->server['SCRIPT_NAME'], PATHINFO_DIRNAME);

				if($basePath !== '/' && stripos($path, $basePath) === 0)
				{
					$path = mb_substr($path, mb_strlen($basePath));
				}

				// Remove "/index.php" from the path

				if(stripos($path, '/index.php') === 0)
				{
					$path = mb_substr($path, 10);
				}

				$path = rawurldecode($path);
			}
		}

		return $this->stripLocaleSegment($languages, $path);
	}

	/**
	 * Determines the request method.
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function determineMethod(): string
	{
		$method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');

		if($method === 'POST')
		{
			return strtoupper($this->post['REQUEST_METHOD_OVERRIDE'] ?? $this->server['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? 'POST');
		}

		return $method;
	}

	/**
	 * Parses a accpet header and returns the values in descending order of preference.
	 *
	 * @access  protected
	 * @param   string
	 * @return  array
	 */
	protected function parseAcceptHeader(string $headerValue): array
	{
		$groupedAccepts = [];

		// Collect acceptable values

		foreach(explode(',', $headerValue) as $accept)
		{
			$quality = 1;

			if(strpos($accept, ';'))
			{
				// We have a quality so we need to split some more

				list($accept, $quality) = explode(';', $accept, 2);

				// Strip the "q=" part so that we're left with only the numeric value

				$quality = substr(trim($quality), 2);
			}

			$groupedAccepts[$quality][] = trim($accept);
		}

		// Sort in descending order of preference

		krsort($groupedAccepts);

		// Flatten array and return it

		return array_merge(...array_values($groupedAccepts));
	}

	/**
	 * Returns all the request headers.
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function collectHeaders(): array
	{
		$headers = [];

		foreach($this->server as $key => $value)
		{
			if(strpos($key, 'HTTP_') === 0)
			{
				$headers[substr($key, 5)] = $value;
			}
			elseif(in_array($key, ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE']))
			{
				$headers[$key] = $value;
			}
		}

		return $headers;
	}

	/**
	 * Collects information about the request.
	 *
	 * @access protected
	 */
	protected function collectRequestInfo()
	{
		// Is this an Ajax request?

		$this->isAjax = (isset($this->server['HTTP_X_REQUESTED_WITH']) && ($this->server['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'));

		// Was the request made using HTTPS?

		$this->isSecure = (!empty($this->server['HTTPS']) && filter_var($this->server['HTTPS'], FILTER_VALIDATE_BOOLEAN)) ? true : false;

		// Is PHP running as a CGI program?

		$this->isCGI = strpos(PHP_SAPI, 'cgi') !== false;

		// Get the real request method that was used

		$this->realMethod = isset($this->server['REQUEST_METHOD']) ? strtoupper($this->server['REQUEST_METHOD']) : 'GET';
	}

	/**
	 * Set the route that matched the request.
	 *
	 * @access  public
	 * @param   \mako\http\routing\Route  $route  Route
	 */
	public function setRoute(Route $route)
	{
		$this->route = $route;
	}

	/**
	 * Returns the route that matched the request.
	 *
	 * @access  public
	 * @return  null|\mako\http\routing\Route
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * Sets a request attribute.
	 *
	 * @access  public
	 * @param   string  $name   Attribute name
	 * @param   mixed   $value  Attribute value
	 */
	public function setAttribute(string $name, $value)
	{
		$this->attributes[$name] = $value;
	}

	/**
	 * Gets a request attribute.
	 *
	 * @access  public
	 * @param   string      $name     Attribute name
	 * @param   null|mixed  $default  Default value
	 * @return  null|mixed
	 */
	public function getAttribute(string $name, $default = null)
	{
		return Arr::get($this->attributes, $name, $default);
	}

	/**
	 * Returns the raw request body.
	 *
	 * @access  public
	 * @return  string
	 */
	public function body(): string
	{
		if($this->body === null)
		{
			$this->body = file_get_contents('php://input');
		}

		return $this->body;
	}

	/**
	 * Parses the request body and returns the chosen value.
	 *
	 * @access  protected
	 * @param   null|string  $key      Array key
	 * @param   null|mixed   $default  Default value
	 * @return  null|mixed
	 */
	protected function getParsed(string $key = null, $default = null)
	{
		if($this->parsedBody === null)
		{
			switch($this->header('content-type'))
			{
				case 'application/x-www-form-urlencoded':
					parse_str($this->body(), $this->parsedBody);
					break;
				case 'text/json':
				case 'application/json':
					$this->parsedBody = json_decode($this->body(), true);
					break;
				default:
					$this->parsedBody = [];
			}

		}

		return ($key === null) ? $this->parsedBody : Arr::get($this->parsedBody, $key, $default);
	}

	/**
	 * Creates a UploadedFile object.
	 *
	 * @access  protected
	 * @param   array                    $file  File info
	 * @return  \mako\http\UploadedFile
	 */
	protected function createUploadedFile(array $file): UploadedFile
	{
		return new UploadedFile($file['tmp_name'], $file['name'], $file['size'], $file['type'], $file['error']);
	}

	/**
	 * Normalizes a multi file upload array to a more manageable format.
	 *
	 * @access  protected
	 * @param   array      $file  File upload array
	 * @return  array
	 */
	protected function normalizeMultiUpload(array $files): array
	{
		$normalized = [];

		$keys = array_keys($files);

		$count = count($files['name']);

		for($i = 0; $i < $count; $i++)
		{
			foreach($keys as $key)
			{
				$normalized[$i][$key] = $files[$key][$i];
			}
		}

		return $normalized;
	}

	/**
	 * Returns an UploadedFile object or an array of UploadedFile objects.
	 *
	 * @access  protected
	 * @param   null|string                    $key      Array key
	 * @param  	null|mixed                     $default  Default value
	 * @return  \mako\http\UploadedFile|array
	 */
	protected function getUploadedFile(string $key = null, $default = null)
	{
		if($this->uploadedFiles === null)
		{
			$this->uploadedFiles = [];

			foreach($this->files as $name => $file)
			{
				if(is_array($file['name']))
				{
					foreach($this->normalizeMultiUpload($file) as $file)
					{
						$this->uploadedFiles[$name][] = $this->createUploadedFile($file);
					}
				}
				else
				{
					$this->uploadedFiles[$name] = $this->createUploadedFile($file);
				}
			}
		}

		return ($key === null) ? $this->uploadedFiles : Arr::get($this->uploadedFiles, $key, $default);
	}

	/**
	 * Fetch data from the GET parameters.
	 *
	 * @access  public
	 * @param   string      $key      Array key
	 * @param   null|mixed  $default  Default value
	 * @return  null|mixed
	 */
	public function get(string $key = null, $default = null)
	{
		return ($key === null) ? $this->get : Arr::get($this->get, $key, $default);
	}

	/**
	 * Fetch data from the POST parameters.
	 *
	 * @access  public
	 * @param   string      $key      Array key
	 * @param   null|mixed  $default  Default value
	 * @return  null|mixed
	 */
	public function post(string $key = null, $default = null)
	{
		return ($key === null) ? $this->post : Arr::get($this->post, $key, $default);
	}

	/**
	 * Fetch data from the PUT parameters.
	 *
	 * @access  public
	 * @param   string      $key      Array key
	 * @param   null|mixed  $default  Default value
	 * @return  null|mixed
	 */
	public function put(string $key = null, $default = null)
	{
		return $this->getParsed($key, $default);
	}

	/**
	 * Fetch data from the PATCH parameters.
	 *
	 * @access  public
	 * @param   string      $key      Array key
	 * @param   null|mixed  $default  Default value
	 * @return  null|mixed
	 */
	public function patch(string $key = null, $default = null)
	{
		return $this->getParsed($key, $default);
	}

	/**
	 * Fetch data from the DELETE parameters.
	 *
	 * @access  public
	 * @param   string      $key      Array key
	 * @param   null|mixed  $default  Default value
	 * @return  null|mixed
	 */
	public function delete(string $key = null, $default = null)
	{
		return $this->getParsed($key, $default);
	}

	/**
	 * Fetch signed cookie data.
	 *
	 * @access  public
	 * @param   string      $name     Cookie name
	 * @param   null|mixed  $default  Default value
	 * @return  null|mixed
	 */
	public function signedCookie(string $name = null, $default = null)
	{
		if(empty($this->signer))
		{
			throw new RuntimeException(vsprintf("%s(): A [ Signer ] instance is required to read signed cookies.", [__METHOD__]));
		}

		if(isset($this->cookies[$name]) && ($value = $this->signer->validate($this->cookies[$name])) !== false)
		{
			return $value;
		}

		return $default;
	}

	/**
	 * Fetch unsigned cookie data.
	 *
	 * @access  public
	 * @param   string      $name     Cookie name
	 * @param   null|mixed  $default  Default value
	 * @return  null|mixed
	 */
	public function cookie(string $name = null, $default = null)
	{
		return $this->cookies[$name] ?? $default;
	}

	/**
	 * Fetch uploaded file.
	 *
	 * @access  public
	 * @param   string      $key      Array key
	 * @param   null|mixed  $default  Default value
	 * @return  null|mixed
	 */
	public function file(string $key = null, $default = null)
	{
		return $this->getUploadedFile($key, $default);
	}

	/**
	 * Fetch server info.
	 *
	 * @access  public
	 * @param   string      $key      Array key
	 * @param   null|mixed  $default  Default value
	 * @return  null|mixed
	 */
	public function server(string $key = null, $default = null)
	{
		return ($key === null) ? $this->server : Arr::get($this->server, $key, $default);
	}

	/**
	 * Checks if the keys exist in the data of the current request method.
	 *
	 * @access  public
	 * @param   string  $key  Array key
	 * @return  bool
	 */
	public function has(string $key): bool
	{
		$method = strtolower($this->realMethod);

		return Arr::has($this->$method(), $key);
	}

	/**
	 * Fetch data the current request method.
	 *
	 * @access  public
	 * @param   string      $key      Array key
	 * @param   null|mixed  $default  Default value
	 * @return  null|mixed
	 */
	public function data(string $key = null, $default = null)
	{
		$method = strtolower($this->realMethod);

		return $this->$method($key, $default);
	}

	/**
	 * Returns request data where keys not in the whitelist have been removed.
	 *
	 * @access  public
	 * @param   array  $keys      Keys to whitelist
	 * @param   array  $defaults  Default values
	 * @return  array
	 */
	public function whitelisted(array $keys, array $defaults = []): array
	{
		return array_intersect_key($this->data(), array_flip($keys)) + $defaults;
	}

	/**
	 * Returns request data where keys in the blacklist have been removed.
	 *
	 * @access  public
	 * @param   array  $keys      Keys to whitelist
	 * @param   array  $defaults  Default values
	 * @return  array
	 */
	public function blacklisted(array $keys, array $defaults = []): array
	{
		return array_diff_key($this->data(), array_flip($keys)) + $defaults;
	}

	/**
	 * Returns a request header.
	 *
	 * @access  public
	 * @param   string      $name     Header name
	 * @param   null|mixed  $default  Default value
	 * @return  null|mixed
	 */
	public function header(string $name, $default = null)
	{
		$name = strtoupper(str_replace('-', '_', $name));

		return $this->headers[$name] ?? $default;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 *
	 * @access  public
	 * @return  array
	 */
	public function acceptableContentTypes(): array
	{
		if(empty($this->acceptableContentTypes))
		{
			$this->acceptableContentTypes = $this->parseAcceptHeader($this->header('accept'));
		}

		return $this->acceptableContentTypes;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 *
	 * @access  public
	 * @return  array
	 */
	public function acceptableLanguages(): array
	{
		if(empty($this->acceptableLanguages))
		{
			$this->acceptableLanguages = $this->parseAcceptHeader($this->header('accept-language'));
		}

		return $this->acceptableLanguages;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 *
	 * @access  public
	 * @return  array
	 */
	public function acceptableCharsets(): array
	{
		if(empty($this->acceptableCharsets))
		{
			$this->acceptableCharsets = $this->parseAcceptHeader($this->header('accept-charset'));
		}

		return $this->acceptableCharsets;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 *
	 * @access  public
	 * @return  array
	 */
	public function acceptableEncodings(): array
	{
		if(empty($this->acceptableEncodings))
		{
			$this->acceptableEncodings = $this->parseAcceptHeader($this->header('accept-encoding'));
		}

		return $this->acceptableEncodings;
	}

	/**
	 * Set the trusted proxies.
	 *
	 * @access  public
	 * @param   array  $trustedProxies  Array of trusted proxy IP addresses
	 */
	public function setTrustedProxies(array $trustedProxies)
	{
		$this->trustedProxies = $trustedProxies;
	}

	/**
	 * Returns the ip of the client that made the request.
	 *
	 * @access  public
	 * @return  string
	 */
	public function ip(): string
	{
		if(empty($this->ip))
		{
			$ip = $this->server('REMOTE_ADDR');

			if(!empty($this->trustedProxies))
			{
				$ips = $this->server('HTTP_X_FORWARDED_FOR');

				if(!empty($ips))
				{
					$ips = array_map('trim', explode(',', $ips));

					foreach($ips as $key => $value)
					{
						foreach($this->trustedProxies as $trustedProxy)
						{
							if(IP::inRange($value, $trustedProxy))
							{
								unset($ips[$key]);

								break;
							}
						}
					}

					$ip = end($ips);
				}
			}

			$this->ip = (filter_var($ip, FILTER_VALIDATE_IP) !== false) ? $ip : '127.0.0.1';
		}

		return $this->ip;
	}

	/**
	 * Returns TRUE if the request was made using Ajax and FALSE if not.
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isAjax(): bool
	{
		return $this->isAjax;
	}

	/**
	 * Returns TRUE if the request was made using HTTPS and FALSE if not.
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isSecure(): bool
	{
		return $this->isSecure;
	}

	/**
	 * Returns TRUE if the request method is considered safe and FALSE if not.
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isSafe(): bool
	{
		return in_array($this->method, ['GET', 'HEAD']);
	}

	/**
	 * Is PHP running as a CGI program?
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isCGI(): bool
	{
		return $this->isCGI;
	}

	/**
	 * Returns the base url of the request.
	 *
	 * @access  public
	 * @return  string
	 */
	public function baseURL(): string
	{
		if(empty($this->baseURL))
		{
			// Get the protocol

			$protocol = $this->isSecure ? 'https://' : 'http://';

			// Get the server name and port

			if(($host = $this->header('host')) === null)
			{
				$host = $this->server('SERVER_NAME');

				$port = $this->server('SERVER_PORT');

				if($port !== null && $port != 80)
				{
					$host = $host . ':' . $port;
				}
			}

			// Get the base path

			$path = $this->server('SCRIPT_NAME');

			$path = str_replace(basename($path), '', $path);

			// Put them all together

			$this->baseURL = rtrim($protocol . $host . $path, '/');
		}

		return $this->baseURL;
	}

	/**
	 * Returns the request path.
	 *
	 * @access  public
	 * @return  string
	 */
	public function path(): string
	{
		return $this->path;
	}

	/**
	 * Returns the request language.
	 *
	 * @access  public
	 * @return  null|array
	 */
	public function language()
	{
		return $this->language;
	}

	/**
	 * Returns the request language prefix.
	 *
	 * @access  public
	 * @return  null|string
	 */
	public function languagePrefix()
	{
		return $this->languagePrefix;
	}

	/**
	 * Returns the request method that was used.
	 *
	 * @access  public
	 * @return  string
	 */
	public function method(): string
	{
		return $this->method;
	}

	/**
	 * Returns the real request method that was used.
	 *
	 * @access  public
	 * @return  string
	 */
	public function realMethod(): string
	{
		return $this->realMethod;
	}

	/**
	 * Returns TRUE if the request method has been faked and FALSE if not.
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isFaked(): bool
	{
		return $this->realMethod !== $this->method;
	}

	/**
	 * Returns the basic HTTP authentication username or NULL.
	 *
	 * @access  public
	 * @return  null|string
	 */
	public function username()
	{
		return $this->server('PHP_AUTH_USER');
	}

	/**
	 * Returns the basic HTTP authentication password or NULL.
	 *
	 * @access  public
	 * @return  null|string
	 */
	public function password()
	{
		return $this->server('PHP_AUTH_PW');
	}

	/**
	 * Returns the referer.
	 *
	 * @access  public
	 * @param   null|mixed  $default  Value to return if no referer is set
	 * @return  null|mixed
	 */
	public function referer($default = null)
	{
		return $this->header('referer', $default);
	}

	/**
	 * Magic shortcut to fetch request data.
	 *
	 * @access  public
	 * @param   string      $key  Array key
	 * @return  null|mixed
	 */
	public function __get(string $key)
	{
		return $this->data($key);
	}

	/**
	 * Magic shortcut to check if request data exists.
	 *
	 * @access  public
	 * @param   string  $key  Array key
	 * @return  bool
	 */
	public function __isset(string $key)
	{
		return $this->data($key) !== null;
	}
}