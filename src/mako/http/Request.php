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
use function strtoupper;
use function trim;

/**
 * Executes requets.
 */
class Request
{
	/**
	 * Remote address fallback.
	 */
	public const string REMOTE_ADDRESS_FALLBACK = '127.0.0.1';

	/**
	 * Script name.
	 */
	protected string $scriptName;

	/**
	 * Get data.
	 */
	public protected(set) Parameters $query;

	/**
	 * Post data.
	 */
	public protected(set) Parameters $post;

	/**
	 * Cookie data.
	 */
	public protected(set) Cookies $cookies;

	/**
	 * File data.
	 */
	public protected(set) Files $files;

	/**
	 * Server info.
	 */
	public protected(set) Server $server;

	/**
	 * Request headers.
	 */
	public protected(set) Headers $headers;

	/**
	 * Raw request body.
	 */
	protected ?string $rawBody = null;

	/**
	 * Parsed request body.
	 */
	public protected(set) ?Body $body = null {
		get {
			if ($this->body === null) {
				$this->body = new Body($this->getRawBody(), $this->getContentType());
			}

			return $this->body;
		}
	}

	/**
	 * Request data of the current request method.
	 */
	public Body|Parameters $data {
		get {
			if ($this->realMethod === 'GET') {
				return $this->query;
			}
			elseif ($this->realMethod === 'POST' && $this->hasFormData()) {
				return $this->post;
			}

			return $this->body;
		}
	}

	/**
	 * Content type.
	 */
	protected ?string $contentType = null;

	/**
	 * Ip address of the client that made the request.
	 */
	protected ?string $ip = null;

	/**
	 * Base path of the request.
	 */
	protected ?string $basePath = null;

	/**
	 * Base URL of the request.
	 */
	protected ?string $baseURL = null;

	/**
	 * Holds the request path.
	 */
	protected string $path;

	/**
	 * Request language.
	 */
	protected ?array $language = null;

	/**
	 * Request language prefix.
	 */
	protected ?string $languagePrefix = null;

	/**
	 * Which request method was used?
	 */
	protected string $method;

	/**
	 * The actual request method that was used.
	 */
	protected string $realMethod;

	/**
	 * Was this request made using HTTPS?
	 */
	protected ?bool $isSecure = null;

	/**
	 * The route that matched the request.
	 */
	protected ?Route $route = null;

	/**
	 * Request attribuntes.
	 */
	protected array $attributes = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		array $request = [],
		?Signer $signer = null,
		?string $scriptName = null
	) {
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

		if ($this->scriptName === 'reactor') {
			$this->scriptName = 'index.php';
		}

		// Set the request path and method

		$languages = $request['languages'] ?? [];

		$this->path = isset($request['path']) ? $this->stripLocaleSegment($languages, $request['path']) : $this->determinePath($languages);

		$this->method = $request['method'] ?? $this->determineMethod();
	}

	/**
	 * Strips the locale segment from the path.
	 */
	protected function stripLocaleSegment(array $languages, string $path): string
	{
		foreach ($languages as $key => $language) {
			if ($path === "/{$key}" || strpos($path, "/{$key}/") === 0) {
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
	 */
	protected function determinePath(array $languages): string
	{
		$path = '/';

		$server = $this->server->all();

		if (!empty($server['PATH_INFO'])) {
			$path = $server['PATH_INFO'];
		}
		elseif (isset($server['REQUEST_URI'])) {
			if ($parsed = parse_url($server['REQUEST_URI'], PHP_URL_PATH)) {
				$path = $parsed;

				// Remove base path from the request path

				$basePath = pathinfo($server['SCRIPT_NAME'], PATHINFO_DIRNAME);

				if ($basePath !== '/' && stripos($path, $basePath) === 0) {
					$path = mb_substr($path, mb_strlen($basePath));
				}

				// Remove "/index.php" from the path

				if (stripos($path, "/{$this->scriptName}") === 0) {
					$path = mb_substr($path, (strlen($this->scriptName) + 1));
				}

				$path = rawurldecode($path);
			}
		}

		return $this->stripLocaleSegment($languages, $path);
	}

	/**
	 * Determines the request method.
	 */
	protected function determineMethod(): string
	{
		$this->realMethod = $method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));

		if ($method === 'POST') {
			return strtoupper($this->post->get('REQUEST_METHOD_OVERRIDE', $this->server->get('HTTP_X_HTTP_METHOD_OVERRIDE', 'POST')));
		}

		return $method;
	}

	/**
	 * Returns the content type of the request body.
	 * An empty string will be returned if the header is missing.
	 */
	public function getContentType(): string
	{
		if ($this->contentType === null) {
			$this->contentType = rtrim(explode(';', (string) $this->headers->get('content-type'))[0]);
		}

		return $this->contentType;
	}

	/**
	 * Returns the base name of the script that handled the request.
	 */
	public function getScriptName(): string
	{
		return $this->scriptName;
	}

	/**
	 * Set the route that matched the request.
	 */
	public function setRoute(Route $route): void
	{
		$this->route = $route;
	}

	/**
	 * Returns the route that matched the request.
	 */
	public function getRoute(): ?Route
	{
		return $this->route;
	}

	/**
	 * Sets a request attribute.
	 */
	public function setAttribute(string $name, mixed $value): void
	{
		Arr::set($this->attributes, $name, $value);
	}

	/**
	 * Gets a request attribute.
	 */
	public function getAttribute(string $name, mixed $default = null): mixed
	{
		return Arr::get($this->attributes, $name, $default);
	}

	/**
	 * Returns the raw request body.
	 */
	public function getRawBody(): string
	{
		if ($this->rawBody === null) {
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
	 */
	public function getQuery(): Parameters
	{
		return $this->query;
	}

	/**
	 * Returns the post data.
	 */
	public function getPost(): Parameters
	{
		return $this->post;
	}

	/**
	 * Returns the cookies.
	 */
	public function getCookies(): Cookies
	{
		return $this->cookies;
	}

	/**
	 * Returns the files.
	 */
	public function getFiles(): Files
	{
		return $this->files;
	}

	/**
	 * Returns the files.
	 */
	public function getServer(): Server
	{
		return $this->server;
	}

	/**
	 * Returns the files.
	 */
	public function getHeaders(): Headers
	{
		return $this->headers;
	}

	/**
	 * Returns the parsed request body.
	 */
	public function getBody(): Body
	{
		return $this->body;
	}

	/**
	 * Returns TRUE if the request has form data and FALSE if not.
	 */
	protected function hasFormData(): bool
	{
		$contentType = $this->getContentType();

		if ($contentType === 'application/x-www-form-urlencoded' || $contentType === 'multipart/form-data') {
			return true;
		}

		return false;
	}

	/**
	 * Returns the data of the current request method.
	 */
	public function getData(): Body|Parameters
	{
		return $this->data;
	}

	/**
	 * Is this IP a trusted proxy?
	 */
	protected function isTrustedProxy(string $ip): bool
	{
		foreach ($this->trustedProxies as $trustedProxy) {
			if (IP::inRange($ip, $trustedProxy)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the ip of the client that made the request.
	 */
	public function getIp(): string
	{
		if ($this->ip === null) {
			$ip = $this->server->get('REMOTE_ADDR');

			if ($ip !== null && $this->isTrustedProxy($ip)) {
				$ips = $this->server->get('HTTP_X_FORWARDED_FOR');

				if (!empty($ips)) {
					$ips = array_reverse(array_map(trim(...), explode(',', $ips)));

					foreach ($ips as $key => $value) {
						if ($this->isTrustedProxy($value) === false) {
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
	 */
	public function isAjax(): bool
	{
		return $this->server->get('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
	}

	/**
	 * Returns TRUE if the request was made using HTTPS and FALSE if not.
	 */
	public function isSecure(): bool
	{
		if ($this->isSecure === null) {
			if ($this->isTrustedProxy($this->server->get('REMOTE_ADDR', static::REMOTE_ADDRESS_FALLBACK)) && $this->server->get('HTTP_X_FORWARDED_PROTO') === 'https') {
				return $this->isSecure = true;
			}

			return $this->isSecure = filter_var($this->server->get('HTTPS', false), FILTER_VALIDATE_BOOLEAN);
		}

		return $this->isSecure;
	}

	/**
	 * Returns TRUE if the request method is considered safe and FALSE if not.
	 */
	public function isSafe(): bool
	{
		return in_array($this->method, ['GET', 'HEAD', 'OPTIONS', 'TRACE']);
	}

	/**
	 * Returns TRUE if the request method is considered idempotent and FALSE if not.
	 */
	public function isIdempotent(): bool
	{
		return in_array($this->method, ['DELETE', 'GET', 'HEAD', 'OPTIONS', 'PUT', 'TRACE']);
	}

	/**
	 * Returns TRUE if the request method is considered cacheable and FALSE if not.
	 */
	public function isCacheable(): bool
	{
		return in_array($this->method, ['GET', 'HEAD']);
	}

	/**
	 * Is PHP running as a CGI program?
	 */
	public function isCGI(): bool
	{
		return strpos(PHP_SAPI, 'cgi') !== false;
	}

	/**
	 * Returns the base path of the request.
	 */
	public function getBasePath(): string
	{
		if ($this->basePath === null) {
			$path = $this->server->get('SCRIPT_NAME', '');

			$this->basePath = rtrim(str_replace(basename($path), '', $path), '/');
		}

		return $this->basePath;
	}

	/**
	 * Returns the base url of the request.
	 */
	public function getBaseURL(): string
	{
		if ($this->baseURL === null) {
			// Get the protocol

			$protocol = $this->isSecure() ? 'https://' : 'http://';

			// Get the server name and port

			if (($host = $this->server->get('HTTP_HOST')) === null) {
				$host = $this->server->get('SERVER_NAME');

				$port = $this->server->get('SERVER_PORT');

				if ($port !== null && $port != 80) {
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
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * Returns TRUE if the resource was requested with a "clean" URL and FALSE if not.
	 */
	public function isClean(): bool
	{
		return strpos($this->server->get('REQUEST_URI', ''), $this->server->get('SCRIPT_NAME', '')) !== 0;
	}

	/**
	 * Returns the request language.
	 */
	public function getLanguage(): ?array
	{
		return $this->language;
	}

	/**
	 * Returns the request language prefix.
	 */
	public function getLanguagePrefix(): ?string
	{
		return $this->languagePrefix;
	}

	/**
	 * Returns the request method that was used.
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

	/**
	 * Returns the real request method that was used.
	 */
	public function getRealMethod(): string
	{
		return $this->realMethod;
	}

	/**
	 * Returns TRUE if the request method has been faked and FALSE if not.
	 */
	public function isFaked(): bool
	{
		return $this->realMethod !== $this->method;
	}

	/**
	 * Returns the basic HTTP authentication username or null.
	 */
	public function getUsername(): ?string
	{
		return $this->server->get('PHP_AUTH_USER');
	}

	/**
	 * Returns the basic HTTP authentication password or null.
	 */
	public function getPassword(): ?string
	{
		return $this->server->get('PHP_AUTH_PW');
	}

	/**
	 * Returns the referrer.
	 */
	public function getReferrer(mixed $default = null): mixed
	{
		return $this->headers->get('referer', $default); // Referrer should be left misspelled here as it's a part of the HTTP spec
	}
}
