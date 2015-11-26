<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http;

use Closure;
use RuntimeException;

use mako\http\Request;
use mako\http\responses\File;
use mako\http\responses\Redirect;
use mako\http\responses\ResponseContainerInterface;
use mako\http\responses\Stream;
use mako\security\Signer;
use mako\view\View;

/**
 * HTTP response.
 *
 * @author  Frederic G. Østby
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
	 * @var boolean
	 */

	protected $outputCompression = false;

	/**
	 * Enable response cache?
	 *
	 * @var boolean
	 */

	protected $responseCache = false;

	/**
	 * Output filters.
	 *
	 * @var array
	 */

	protected $outputFilters = [];

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

		// 2xx Success

		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',

		// 3xx Redirection

		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		//306 => 'Switch Proxy',
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
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		421 => 'There are too many connections from your internet address',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Unordered Collection',
		426 => 'Upgrade Required',
		429 => 'Too Many Requests',
		449 => 'Retry With',
		450 => 'Blocked by Windows Parental Controls',
		498 => 'Invalid or expired token',

		// 5xx Server Error

		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		509 => 'Bandwidth Limit Exceeded',
		510 => 'Not Extended',
		511 => 'Network Authentication Required',
		530 => 'User access denied',
	];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\http\Request     $request  Request instance
	 * @param   string                 $charset  Response charset
	 * @param   \mako\security\Signer  $signer   Signer instance used to sign cookies
	 */

	public function __construct(Request $request, $charset = 'UTF-8', Signer $signer = null)
	{
		$this->request = $request;

		$this->charset = $charset;

		$this->signer = $signer;
	}

	/**
	 * Sets the response body.
	 *
	 * @access  public
	 * @param   mixed                $body  Response body
	 * @return  \mako\http\Response
	 */

	public function body($body)
	{
		if($body instanceof View)
		{
			$this->body = $body->render();
		}
		elseif($body instanceof $this)
		{
			$this->body = $body->getBody();

			$this->statusCode = $body->getStatus();

			$this->outputFilters = array_merge($this->outputFilters, $body->getFilters());

			$this->headers = $this->headers + $body->getHeaders();

			$this->cookies = $this->cookies + $body->getCookies();
		}
		else
		{
			$this->body = $body;
		}

		return $this;
	}

	/**
	 * Returns the response body.
	 *
	 * @access  public
	 * @return  mixed
	 */

	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Clears the response body.
	 *
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function clearBody()
	{
		$this->body = null;

		return $this;
	}

	/**
	 * Sets the response content type.
	 *
	 * @access  public
	 * @param   string                $contentType  Content type
	 * @param   string                $charset      Charset
	 * @return  \mako\http\Response
	 */

	public function type($contentType, $charset = null)
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
	 * @access  public
	 * @return  string
	 */

	public function getType()
	{
		return $this->contentType;
	}

	/**
	 * Sets the response charset.
	 *
	 * @access  public
	 * @param   string               $charset  Charset
	 * @return  \mako\http\Response
	 */

	public function charset($charset)
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Returns the response charset.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getCharset()
	{
		return $this->charset;
	}

	/**
	 * Sets the HTTP status code.
	 *
	 * @access  public
	 * @param   int                  $statusCode  HTTP status code
	 * @return  \mako\http\Response
	 */

	public function status($statusCode)
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
	 * @access  public
	 * @return  int
	 */

	public function getStatus()
	{
		return $this->statusCode;
	}

	/**
	 * Adds output filter that all output will be passed through before being sent.
	 *
	 * @access  public
	 * @param   \Closure             $filter  Closure used to filter output
	 * @return  \mako\http\Response
	 */

	public function filter(Closure $filter)
	{
		$this->outputFilters[] = $filter;

		return $this;
	}

	/**
	 * Returns the response filters.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getFilters()
	{
		return $this->outputFilters;
	}

	/**
	 * Clears all output filters.
	 *
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function clearFilters()
	{
		$this->outputFilters = [];

		return $this;
	}

	/**
	 * Sets a response header.
	 *
	 * @access  public
	 * @param   string               $name     Header name
	 * @param   string               $value    Header value
	 * @param   boolean              $replace  Replace header?
	 * @return  \mako\http\Response
	 */

	public function header($name, $value, $replace = true)
	{
		$name = strtolower($name);

		if($replace === true)
		{
			$this->headers[$name] = [$value];
		}
		else
		{
			$headers = isset($this->headers[$name]) ? $this->headers[$name] : [];

			$this->headers[$name] = array_merge($headers, [$value]);
		}

		return $this;
	}

	/**
	 * Checks if the header exists in the response.
	 *
	 * @access  public
	 * @param   string   $name  Header name
	 * @return  boolean
	 */

	public function hasHeader($name)
	{
		return isset($this->headers[strtolower($name)]);
	}

	/**
	 * Removes a response header.
	 *
	 * @access  public
	 * @param   string               $name   Header name
	 * @return  \mako\http\Response
	 */

	public function removeHeader($name)
	{
		unset($this->headers[strtolower($name)]);

		return $this;
	}

	/**
	 * Returns the response headers.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Clear the response headers.
	 *
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function clearHeaders()
	{
		$this->headers = [];

		return $this;
	}

	/**
	 * Sets an unsigned cookie.
	 *
	 * @access  public
	 * @param   string               $name     Cookie name
	 * @param   string               $value    Cookie value
	 * @param   int                  $ttl      Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param   array                $options  Cookie options
	 * @return  \mako\http\Response
	 */

	public function cookie($name, $value, $ttl = 0, array $options = [])
	{
		$ttl = ($ttl === 0) ? 0 : (time() + $ttl);

		$defaults = ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false];

		$this->cookies[$name] = ['name' => $name, 'value' => $value, 'ttl' => $ttl] + $options + $defaults;

		return $this;
	}

	/**
	 * Sets a signed cookie.
	 *
	 * @access  public
	 * @param   string               $name     Cookie name
	 * @param   string               $value    Cookie value
	 * @param   int                  $ttl      Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param   array                $options  Cookie options
	 * @return  \mako\http\Response
	 */

	public function signedCookie($name, $value, $ttl = 0, array $options = [])
	{
		if(empty($this->signer))
		{
			throw new RuntimeException(vsprintf("%s(): A [ Signer ] instance is required to read signed cookies.", [__METHOD__]));
		}

		return $this->cookie($name, $this->signer->sign($value), $ttl, $options);
	}

	/**
	 * Deletes a cookie.
	 *
	 * @access  public
	 * @param   string               $name     Cookie name
	 * @param   array                $options  Cookie options
	 * @return  \mako\http\Response
	 */

	public function deleteCookie($name, array $options = [])
	{
		return $this->cookie($name, '', -3600, $options);
	}

	/**
	 * Checks if the cookie exists in the response.
	 *
	 * @access  public
	 * @param   string   $name  Cookie name
	 * @return  boolean
	 */

	public function hasCookie($name)
	{
		return isset($this->cookies[$name]);
	}

	/**
	 * Removes a cookie from the response.
	 *
	 * @access  public
	 * @param   string               $name   Cookie name
	 * @return  \mako\http\Response
	 */

	public function removeCookie($name)
	{
		unset($this->cookies[$name]);

		return $this;
	}

	/**
	 * Returns the response cookies.
	 *
	 * @access  public
	 * @return array
	 */

	public function getCookies()
	{
		return $this->cookies;
	}

	/**
	 * Clear cookies.
	 *
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function clearCookies()
	{
		$this->cookies = [];

		return $this;
	}

	/**
	 * Clears the response body, filters, cookies and headers.
	 *
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function clear()
	{
		$this->clearBody();
		$this->clearFilters();
		$this->clearHeaders();
		$this->clearCookies();

		return $this;
	}

	/**
	 * Sends response headers.
	 *
	 * @access  protected
	 */

	public function sendHeaders()
	{
		// Send status header

		$protocol = $this->request->server('SERVER_PROTOCOL', 'HTTP/1.1');

		header($protocol . ' ' . $this->statusCode . ' ' . $this->statusCodes[$this->statusCode]);

		// Send content type header

		$contentType = $this->contentType;

		if(stripos($contentType, 'text/') === 0 || in_array($contentType, ['application/json', 'application/xml']))
		{
			$contentType .= '; charset=' . $this->charset;
		}

		header('Content-Type: ' . $contentType);

		// Send other headers

		foreach($this->headers as $name => $headers)
		{
			foreach($headers as $value)
			{
				header($name . ': ' . $value, false);
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
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function cache()
	{
		$this->responseCache = true;

		return $this;
	}

	/**
	 * Disables ETag response cache.
	 *
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function disableCaching()
	{
		$this->responseCache = false;

		return $this;
	}

	/**
	 * Enables output compression.
	 *
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function compress()
	{
		$this->outputCompression = true;

		return $this;
	}

	/**
	 * Disables output compression.
	 *
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function disableCompression()
	{
		$this->outputCompression = false;

		return $this;
	}

	/**
	 * Returns a file container.
	 *
	 * @access  public
	 * @param   string                    $file     File path
	 * @param   array                     $options  Options
	 * @return  \mako\http\response\File
	 */

	public function file($file, array $options = [])
	{
		return new File($file, $options);
	}

	/**
	 * Returns a stream container.
	 *
	 * @access  public
	 * @param   \Closure                    $stream  Stream
	 * @return  \mako\http\response\Stream
	 */

	public function stream(Closure $stream)
	{
		return new Stream($stream);
	}

	/**
	 * Redirects to another location.
	 *
	 * @access  public
	 * @param   string                        $location  Location
	 * @return  \mako\http\response\Redirect
	 */

	public function redirect($location)
	{
		return new Redirect($location);
	}

	/**
	 * Redirects the user back to the previous page.
	 *
	 * @access  public
	 * @param   int                           $statusCode  HTTP status code
	 * @return  \mako\http\response\Redirect
	 */

	public function back($statusCode = 302)
	{
		return $this->redirect($this->request->referer())->status($statusCode);
	}

	/**
	 * Send output to browser.
	 *
	 * @access  public
	 */

	public function send()
	{
		if($this->body instanceof ResponseContainerInterface)
		{
			// This is a response container so we'll just pass it the
			// request and response instances and let it handle the rest itself

			$this->body->send($this->request, $this);
		}
		else
		{
			$sendBody = true;

			// Make sure that output buffering is enabled

			if(ob_get_level() === 0)
			{
				ob_start();
			}

			// Cast body to string so that everything is rendered
			// before running through response filters

			$this->body = (string) $this->body;

			// Run body through the response filters

			foreach($this->outputFilters as $outputFilter)
			{
				$this->body = $outputFilter($this->body);
			}

			// Check ETag if response cache is enabled

			if($this->responseCache === true)
			{
				$hash = '"' . hash('sha256', $this->body) . '"';

				$this->header('ETag', $hash);

				if(str_replace('-gzip', '', $this->request->header('if-none-match')) === $hash)
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
					$this->header('content-length', ob_get_length());
				}
			}

			// Send the headers and flush the output buffer

			$this->sendHeaders();

			ob_end_flush();
		}
	}
}