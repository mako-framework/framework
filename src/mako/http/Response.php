<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http;

use mako\http\response\builders\ResponseBuilderInterface;
use mako\http\response\Cookies;
use mako\http\response\Headers;
use mako\http\response\senders\ResponseSenderInterface;
use mako\security\Signer;

use function hash;
use function header;
use function in_array;
use function ob_end_flush;
use function ob_get_length;
use function ob_get_level;
use function ob_start;
use function setcookie;
use function setrawcookie;
use function str_replace;
use function stripos;

/**
 * HTTP response.
 */
class Response
{
	/**
	 * Default status code.
	 *
	 * @var int
	 */
	public const DEFAULT_STATUS = 200;

	/**
	 * Request instance.
	 *
	 * @var \mako\http\Request
	 */
	protected $request;

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
	 * Response character set.
	 *
	 * @var string
	 */
	protected $charset;

	/**
	 * Status code.
	 *
	 * @var int
	 */
	protected $statusCode = self::DEFAULT_STATUS;

	/**
	 * Response headers.
	 *
	 * @var \mako\http\response\Headers
	 */
	protected $headers;

	/**
	 * Cookies.
	 *
	 * @var \mako\http\response\Cookies
	 */
	protected $cookies;

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
		103 => 'Early Hints',

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
		306 => 'Switch Proxy',
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
		425 => 'Too Early',
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
	 * @param string                     $charset Response character set
	 * @param \mako\security\Signer|null $signer  Signer instance used to sign cookies
	 */
	public function __construct(Request $request, string $charset = 'UTF-8', ?Signer $signer = null)
	{
		$this->request = $request;

		$this->charset = $charset;

		$this->headers = new Headers;

		$this->cookies = new Cookies($signer);
	}

	/**
	 * Returns the request instance.
	 *
	 * @return \mako\http\Request
	 */
	public function getRequest(): Request
	{
		return $this->request;
	}

	/**
	 * Sets the response body.
	 *
	 * @param  mixed               $body Response body
	 * @return \mako\http\Response
	 */
	public function setBody($body): Response
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
	public function setType(string $contentType, ?string $charset = null): Response
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
	 * Sets the response character set.
	 *
	 * @param  string              $charset Character set
	 * @return \mako\http\Response
	 */
	public function setCharset(string $charset): Response
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Returns the response character set.
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
	public function setStatus(int $statusCode): Response
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
	 * Returns response header collection.
	 *
	 * @return \mako\http\response\Headers
	 */
	public function getHeaders(): Headers
	{
		return $this->headers;
	}

	/**
	 * Returns response cookie collection.
	 *
	 * @return \mako\http\response\Cookies
	 */
	public function getCookies(): Cookies
	{
		return $this->cookies;
	}

	/**
	 * Clears the response body, cookies and headers.
	 *
	 * @return \mako\http\Response
	 */
	public function clear(): Response
	{
		$this->clearBody();

		$this->headers->clear();

		$this->cookies->clear();

		return $this;
	}

	/**
	 * Clears the response body in addition to cookies and headers that don't match the provided names or patterns.
	 *
	 * @param  array               $exceptions Exceptions
	 * @return \mako\http\Response
	 */
	public function clearExcept(array $exceptions): Response
	{
		$this->clearBody();

		empty($exceptions['headers']) ? $this->headers->clear() : $this->headers->clearExcept($exceptions['headers']);

		empty($exceptions['cookies']) ? $this->cookies->clear() : $this->cookies->clearExcept($exceptions['cookies']);

		return $this;
	}

	/**
	 * Resets the response.
	 *
	 * @return \mako\http\Response
	 */
	public function reset(): Response
	{
		$this->statusCode = self::DEFAULT_STATUS;

		return $this->clear();
	}

	/**
	 * Resets the response except for cookies and headers that match the provided names or patterns.
	 *
	 * @param  array               $exceptions Exceptions
	 * @return \mako\http\Response
	 */
	public function resetExcept(array $exceptions): Response
	{
		$this->statusCode = self::DEFAULT_STATUS;

		return $this->clearExcept($exceptions);
	}

	/**
	 * Sends response headers.
	 */
	public function sendHeaders(): void
	{
		// Send status header

		$protocol = $this->request->getServer()->get('SERVER_PROTOCOL', 'HTTP/1.1');

		header("{$protocol} {$this->statusCode} {$this->statusCodes[$this->statusCode]}");

		// Send content type header

		$contentType = $this->contentType;

		if(stripos($contentType, 'text/') === 0 || in_array($contentType, ['application/json', 'application/xml', 'application/rss+xml', 'application/atom+xml']))
		{
			$contentType .= "; charset={$this->charset}";
		}

		header("Content-Type: {$contentType}");

		// Send other headers

		foreach($this->headers->all() as $name => $values)
		{
			foreach($values as $value)
			{
				header("{$name}: {$value}", false);
			}
		}

		// Send cookie headers

		foreach($this->cookies->all() as $cookie)
		{
			['raw' => $raw, 'name' => $name, 'value' => $value, 'options' => $options] = $cookie;

			if($raw)
			{
				setrawcookie($name, $value, $options);
			}
			else
			{
				setcookie($name, $value, $options);
			}
		}
	}

	/**
	 * Is the response cacheable?
	 *
	 * @return bool
	 */
	public function isCacheable(): bool
	{
		if($this->request->isCacheable() === false)
		{
			return false;
		}

		if(in_array($this->statusCode, [200, 203, 204, 206, 300, 301, 404, 405, 410, 414, 501]) === false)
		{
			return false;
		}

		if($this->headers->hasValue('Cache-Control', 'no-store', false) || $this->headers->hasValue('Cache-Control', 'private', false))
		{
			return false;
		}

		return true;
	}

	/**
	 * Enables ETag response cache.
	 *
	 * @return \mako\http\Response
	 */
	public function enableCaching(): Response
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
	public function enableCompression(): Response
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
	public function send(): void
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

			if($this->responseCache === true && $this->isCacheable())
			{
				$hash = '"' . hash('sha256', $this->body) . '"';

				$this->headers->add('ETag', $hash);

				if(str_replace('-gzip', '', $this->request->getHeaders()->get('If-None-Match')) === $hash)
				{
					$this->setStatus(304);

					$sendBody = false;
				}
			}

			if($sendBody && in_array($this->statusCode, [100, 101, 102, 103, 204, 304]) === false)
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

				if(!$this->headers->has('Transfer-Encoding'))
				{
					$this->headers->add('Content-Length', ob_get_length());
				}
			}

			// Send the headers and flush the output buffer

			$this->sendHeaders();

			ob_end_flush();
		}
	}
}
