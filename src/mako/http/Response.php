<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http;

use RuntimeException;

use mako\http\Request;
use mako\http\response\builders\ResponseBuilderInterface;
use mako\http\response\senders\ResponseSenderInterface;
use mako\security\Signer;

/**
 * HTTP response.
 *
 * @author Frederic G. Østby
 */
class Response
{
	/**
	 * Request instance.
	 *
	 * @var \mako\http\Request
	 */
	protected $request;

	/**
	 * Signer instance.
	 *
	 * @var \mako\security\Signer
	 */
	protected $signer;

	/**
	 * Response body.
	 *
	 * @var mixed
	 */
	protected $body;

	/**
	 * Response content type.
	 *
	 * @var string
	 */
	protected $contentType = 'text/html';

	/**
	 * Response charset.
	 *
	 * @var string
	 */
	protected $charset;

	/**
	 * Status code.
	 *
	 * @var int
	 */
	protected $statusCode = 200;

	/**
	 * Response headers.
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Cookies.
	 *
	 * @var array
	 */
	protected $cookies = [];

	/**
	 * Compress output?
	 *
	 * @var bool
	 */
	protected $outputCompression = false;

	/**
	 * Enable response cache?
	 *
	 * @var bool
	 */
	protected $responseCache = false;

	/**
	 * HTTP status codes.
	 *
	 * @var array
	 */
	protected $statusCodes =
	[
		// 1xx Informational

		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		103 => 'Checkpoint',

		// 2xx Success

		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		208 => 'Already Reported',
		226 => 'IM Used',

		// 3xx Redirection

		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',

		// 4xx Client Error

		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Payload Too Large',
		414 => 'URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		419 => 'Authentication Timeout',
		421 => 'Misdirected Request',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
 		429 => 'Too Many Requests',
 		431 => 'Request Header Fields Too Large',
		449 => 'Retry With',
		450 => 'Blocked by Windows Parental Controls',
		451 => 'Unavailable For Legal Reasons',
		498 => 'Invalid Token',
		499 => 'Token required',

		// 5xx Server Error

		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		509 => 'Bandwidth Limit Exceeded',
		510 => 'Not Extended',
		511 => 'Network Authentication Required',
		530 => 'User access denied',
	];

	/**
	 * Constructor.
	 *
	 * @param \mako\http\Request         $request Request instance
	 * @param string                     $charset Response charset
	 * @param \mako\security\Signer|null $signer  Signer instance used to sign cookies
	 */
	public function __construct(Request $request, string $charset = 'UTF-8', Signer $signer = null)
	{
		$this->request = $request;

		$this->charset = $charset;

		$this->signer = $signer;
	}

	/**
	 * Sets the response body.
	 *
	 * @param  mixed               $body Response body
	 * @return \mako\http\Response
	 */
	public function body($body): Response
	{
		$this->body = $body;

		return $this;
	}

	/**
	 * Returns the response body.
	 *
	 * @return mixed
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Clears the response body.
	 *
	 * @return \mako\http\Response
	 */
	public function clearBody(): Response
	{
		$this->body = null;

		return $this;
	}

	/**
	 * Sets the response content type.
	 *
	 * @param  string              $contentType Content type
	 * @param  string|null         $charset     Charset
	 * @return \mako\http\Response
	 */
	public function type(string $contentType, string $charset = null): Response
	{
		$this->contentType = $contentType;

		if($charset !== null)
		{
			$this->charset = $charset;
		}

		return $this;
	}

	/**
	 * Returns the response content type.
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->contentType;
	}

	/**
	 * Sets the response charset.
	 *
	 * @param  string              $charset Charset
	 * @return \mako\http\Response
	 */
	public function charset(string $charset): Response
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Returns the response charset.
	 *
	 * @return string
	 */
	public function getCharset(): string
	{
		return $this->charset;
	}

	/**
	 * Sets the HTTP status code.
	 *
	 * @param  int                 $statusCode HTTP status code
	 * @return \mako\http\Response
	 */
	public function status(int $statusCode): Response
	{
		if(isset($this->statusCodes[$statusCode]))
		{
			$this->statusCode = $statusCode;
		}

		return $this;
	}

	/**
	 * Returns the HTTP status code.
	 *
	 * @return int
	 */
	public function getStatus(): int
	{
		return $this->statusCode;
	}

	/**
	 * Sets a response header.
	 *
	 * @param  string              $name    Header name
	 * @param  string              $value   Header value
	 * @param  bool                $replace Replace header?
	 * @return \mako\http\Response
	 */
	public function header(string $name, string $value, bool $replace = true): Response
	{
		$normalizedName = strtolower($name);

		$this->headers[$normalizedName]['name'] = $name;

		if($replace === true)
		{
			$this->headers[$normalizedName]['value'] = [$value];
		}
		else
		{
			$headers = $this->headers[$normalizedName]['value'] ?? [];

			$this->headers[$normalizedName]['value'] = array_merge($headers, [$value]);
		}

		return $this;
	}

	/**
	 * Checks if the header exists in the response.
	 *
	 * @param  string $name Header name
	 * @return bool
	 */
	public function hasHeader(string $name): bool
	{
		return isset($this->headers[strtolower($name)]);
	}

	/**
	 * Removes a response header.
	 *
	 * @param  string              $name Header name
	 * @return \mako\http\Response
	 */
	public function removeHeader(string $name): Response
	{
		unset($this->headers[strtolower($name)]);

		return $this;
	}

	/**
	 * Returns the response headers.
	 *
	 * @return array
	 */
	public function getHeaders(): array
	{
		return array_column($this->headers, 'value', 'name');
	}

	/**
	 * Clear the response headers.
	 *
	 * @return \mako\http\Response
	 */
	public function clearHeaders(): Response
	{
		$this->headers = [];

		return $this;
	}

	/**
	 * Sets an unsigned cookie.
	 *
	 * @param  string              $name    Cookie name
	 * @param  string              $value   Cookie value
	 * @param  int                 $ttl     Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param  array               $options Cookie options
	 * @return \mako\http\Response
	 */
	public function cookie(string $name, string $value, int $ttl = 0, array $options = []): Response
	{
		$ttl = ($ttl === 0) ? 0 : (time() + $ttl);

		$defaults = ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false];

		$this->cookies[$name] = ['name' => $name, 'value' => $value, 'ttl' => $ttl] + $options + $defaults;

		return $this;
	}

	/**
	 * Sets a signed cookie.
	 *
	 * @param  string              $name    Cookie name
	 * @param  string              $value   Cookie value
	 * @param  int                 $ttl     Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param  array               $options Cookie options
	 * @return \mako\http\Response
	 */
	public function signedCookie(string $name, string $value, int $ttl = 0, array $options = []): Response
	{
		if(empty($this->signer))
		{
			throw new RuntimeException('A [ Signer ] instance is required to sign cookies.');
		}

		return $this->cookie($name, $this->signer->sign($value), $ttl, $options);
	}

	/**
	 * Deletes a cookie.
	 *
	 * @param  string              $name    Cookie name
	 * @param  array               $options Cookie options
	 * @return \mako\http\Response
	 */
	public function deleteCookie(string $name, array $options = []): Response
	{
		return $this->cookie($name, '', -3600, $options);
	}

	/**
	 * Checks if the cookie exists in the response.
	 *
	 * @param  string $name Cookie name
	 * @return bool
	 */
	public function hasCookie(string $name): bool
	{
		return isset($this->cookies[$name]);
	}

	/**
	 * Removes a cookie from the response.
	 *
	 * @param  string              $name Cookie name
	 * @return \mako\http\Response
	 */
	public function removeCookie(string $name): Response
	{
		unset($this->cookies[$name]);

		return $this;
	}

	/**
	 * Returns the response cookies.
	 *
	 * @return array
	 */
	public function getCookies(): array
	{
		return $this->cookies;
	}

	/**
	 * Clear cookies.
	 *
	 * @return \mako\http\Response
	 */
	public function clearCookies(): Response
	{
		$this->cookies = [];

		return $this;
	}

	/**
	 * Clears the response body, cookies and headers.
	 *
	 * @return \mako\http\Response
	 */
	public function clear(): Response
	{
		$this->clearBody();
		$this->clearHeaders();
		$this->clearCookies();

		return $this;
	}

	/**
	 * Sends response headers.
	 */
	public function sendHeaders()
	{
		// Send status header

		$protocol = $this->request->getServer()->get('SERVER_PROTOCOL', 'HTTP/1.1');

		header($protocol . ' ' . $this->statusCode . ' ' . $this->statusCodes[$this->statusCode]);

		// Send content type header

		$contentType = $this->contentType;

		if(stripos($contentType, 'text/') === 0 || in_array($contentType, ['application/json', 'application/xml', 'application/rss+xml', 'application/atom+xml']))
		{
			$contentType .= '; charset=' . $this->charset;
		}

		header('Content-Type: ' . $contentType);

		// Send other headers

		foreach($this->headers as $header)
		{
			foreach($header['value'] as $value)
			{
				header($header['name'] . ': ' . $value, false);
			}
		}

		// Send cookie headers

		foreach($this->cookies as $cookie)
		{
			setcookie($cookie['name'], $cookie['value'], $cookie['ttl'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
		}
	}

	/**
	 * Enables ETag response cache.
	 *
	 * @return \mako\http\Response
	 */
	public function cache(): Response
	{
		$this->responseCache = true;

		return $this;
	}

	/**
	 * Disables ETag response cache.
	 *
	 * @return \mako\http\Response
	 */
	public function disableCaching(): Response
	{
		$this->responseCache = false;

		return $this;
	}

	/**
	 * Enables output compression.
	 *
	 * @return \mako\http\Response
	 */
	public function compress(): Response
	{
		$this->outputCompression = true;

		return $this;
	}

	/**
	 * Disables output compression.
	 *
	 * @return \mako\http\Response
	 */
	public function disableCompression(): Response
	{
		$this->outputCompression = false;

		return $this;
	}

	/**
	 * Send output to browser.
	 */
	public function send()
	{
		if($this->body instanceof ResponseSenderInterface)
		{
			// This is a response sender so we'll just pass it the
			// request and response instances and let it handle the rest itself

			$this->body->send($this->request, $this);
		}
		else
		{
			if($this->body instanceof ResponseBuilderInterface)
			{
				$this->body->build($this->request, $this);
			}

			$sendBody = true;

			// Make sure that output buffering is enabled

			if(ob_get_level() === 0)
			{
				ob_start();
			}

			// Cast body to string in case it's an obect implementing __toString

			$this->body = (string) $this->body;

			// Check ETag if response cache is enabled

			if($this->responseCache === true)
			{
				$hash = '"' . hash('sha256', $this->body) . '"';

				$this->header('ETag', $hash);

				if(str_replace('-gzip', '', $this->request->getHeaders()->get('if-none-match')) === $hash)
				{
					$this->status(304);

					$sendBody = false;
				}
			}

			if($sendBody && !in_array($this->statusCode, [100, 101, 102, 204, 304]))
			{
				// Start compressed output buffering if output compression is enabled

				if($this->outputCompression)
				{
					ob_start('ob_gzhandler');
				}

				echo $this->body;

				// If output compression is enabled then we'll have to flush the compressed buffer
				// so that we can get the compressed content length when setting the content-length header

				if($this->outputCompression)
				{
					ob_end_flush();
				}

				// Add the content-length header

				if(!array_key_exists('transfer-encoding', $this->headers))
				{
					$this->header('Content-Length', ob_get_length());
				}
			}

			// Send the headers and flush the output buffer

			$this->sendHeaders();

			ob_end_flush();
		}
	}
}
