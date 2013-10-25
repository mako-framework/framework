<?php

namespace mako\http;

use \Closure;
use \mako\core\Config;
use \mako\security\MAC;
use \mako\http\Request;
use \mako\http\StreamContainer;
use \mako\http\routing\URL;

/**
 * HTTP response.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Response
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

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
	
	protected $body = '';

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

	protected $charset = MAKO_CHARSET;

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

	protected $headers = array();

	/**
	 * Cookies.
	 * 
	 * @var array
	 */

	protected $cookies = array();

	/**
	 * Compress output?
	 * 
	 * @var boolean
	 */

	protected $outputCompression;

	/**
	 * Enable response cache?
	 * 
	 * @var boolean
	 */

	protected $responseCache;

	/**
	 * Output filters.
	 *
	 * @var array
	 */
	
	protected $outputFilters = array();
	
	/**
	 * HTTP status codes.
	 *
	 * @var array
	 */
	
	protected $statusCodes = array
	(
		// 1xx Informational
		
		'100' => 'Continue',
		'101' => 'Switching Protocols',
		'102' => 'Processing',
		
		// 2xx Success
		
		'200' => 'OK',
		'201' => 'Created',
		'202' => 'Accepted',
		'203' => 'Non-Authoritative Information',
		'204' => 'No Content',
		'205' => 'Reset Content',
		'206' => 'Partial Content',
		'207' => 'Multi-Status',
		
		// 3xx Redirection
		
		'300' => 'Multiple Choices',
		'301' => 'Moved Permanently',
		'302' => 'Found',
		'303' => 'See Other',
		'304' => 'Not Modified',
		'305' => 'Use Proxy',
		//'306' => 'Switch Proxy',
		'307' => 'Temporary Redirect',
		
		// 4xx Client Error
		
		'400' => 'Bad Request',
		'401' => 'Unauthorized',
		'402' => 'Payment Required',
		'403' => 'Forbidden',
		'404' => 'Not Found',
		'405' => 'Method Not Allowed',
		'406' => 'Not Acceptable',
		'407' => 'Proxy Authentication Required',
		'408' => 'Request Timeout',
		'409' => 'Conflict',
		'410' => 'Gone',
		'411' => 'Length Required',
		'412' => 'Precondition Failed',
		'413' => 'Request Entity Too Large',
		'414' => 'Request-URI Too Long',
		'415' => 'Unsupported Media Type',
		'416' => 'Requested Range Not Satisfiable',
		'417' => 'Expectation Failed',
		'418' => 'I\'m a teapot',
		'421' => 'There are too many connections from your internet address',
		'422' => 'Unprocessable Entity',
		'423' => 'Locked',
		'424' => 'Failed Dependency',
		'425' => 'Unordered Collection',
		'426' => 'Upgrade Required',
		'449' => 'Retry With',
		'450' => 'Blocked by Windows Parental Controls',
		
		// 5xx Server Error
		
		'500' => 'Internal Server Error',
		'501' => 'Not Implemented',
		'502' => 'Bad Gateway',
		'503' => 'Service Unavailable',
		'504' => 'Gateway Timeout',
		'505' => 'HTTP Version Not Supported',
		'506' => 'Variant Also Negotiates',
		'507' => 'Insufficient Storage',
		'509' => 'Bandwidth Limit Exceeded',
		'510' => 'Not Extended',
		'530' => 'User access denied',
	);
	
	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------
	
	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\http\Request  $request  Request instance
	 * @param   string              $body     (optional) Response body
	 */
	
	public function __construct(Request $request, $body = null)
	{
		$this->request = $request;

		$this->body($body);

		$config = Config::get('application');

		$this->outputCompression = $config['compress_output'];
		$this->responseCache     = $config['response_cache'];
	}

	/**
	 * Factory method making method chaining possible right off the bat.
	 *
	 * @access  public
	 * @param   string               $body  (optional) Response body
	 * @return  \mako\http\Response
	 */

	public static function factory($body = null)
	{
		return new static($body);
	}
	
	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sets the response body.
	 *
	 * @access  public
	 * @param   mixed                $body  Response body
	 * @return  \mako\http\Response
	 */

	public function body($body)
	{
		if($body instanceof $this)
		{
			$this->body = $body->getBody();
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
	 * Sets the response content type.
	 * 
	 * @access  public
	 * @param   string                $contentType  Content type
	 * @param   string                $charset      (optional) Charset
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
	 * Clears all output filters.
	 * 
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function clearFilters()
	{
		$this->outputFilters = array();

		return $this;
	}

	/**
	 * Sets a response header.
	 * 
	 * @access  public
	 * @param   string               $name   Header name
	 * @param   string               $value  Header value
	 * @return  \mako\http\Response
	 */

	public function header($name, $value)
	{
		$this->headers[strtolower($name)] = $value;

		return $this;
	}

	/**
	 * Clear the response headers.
	 * 
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function clearHeaders()
	{
		$this->headers = array();

		return $this;
	}

	/**
	 * Sets an unsigned cookie.
	 *
	 * @access  public
	 * @param   string               $name     Cookie name
	 * @param   string               $value    Cookie value
	 * @param   int                  $ttl      (optional) Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param   array                $options  (optional) Cookie options
	 * @return  \mako\http\Response
	 */

	public function unsignedCookie($name, $value, $ttl = 0, array $options = array())
	{
		$ttl = ($ttl > 0) ? (time() + $ttl) : 0;

		$defaults = array('path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false);

		$this->cookies[] = array('name' => $name, 'value' => $value, 'ttl' => $ttl) + $options + $defaults;

		return $this;
	}

	/**
	 * Sets a signed cookie.
	 *
	 * @access  public
	 * @param   string               $name     Cookie name
	 * @param   string               $value    Cookie value
	 * @param   int                  $ttl      (optional) Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param   array                $options  (optional) Cookie options
	 * @return  \mako\http\Response
	 */

	public function cookie($name, $value, $ttl = 0, array $options = array())
	{
		return $this->unsignedCookie($name, MAC::sign($value), $ttl, $options);
	}

	/**
	 * Deletes a cookie.
	 *
	 * @access  public
	 * @param   string               $name     Cookie name
	 * @param   array                $options  (optional) Cookie options
	 * @return  \mako\http\Response
	 */

	public function deleteCookie($name, array $options = array())
	{
		return $this->unsignedCookie($name, '', time() - 3600, $options);
	}

	/**
	 * Clear cookies.
	 * 
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function clearCookies()
	{
		$this->cookies = array();

		return $this;
	}

	/**
	 * Sends response headers.
	 * 
	 * @access  protected
	 */

	protected function sendHeaders()
	{
		// Send status header

		if($this->request->server('FCGI_SERVER_VERSION', false) !== false)
		{
			$protocol = 'Status:';
		}
		else
		{
			$protocol = $this->request->server('SERVER_PROTOCOL', 'HTTP/1.1');
		}

		header($protocol . ' ' . $this->statusCode . ' ' . $this->statusCodes[$this->statusCode]);

		// Send content type header

		$contentType = $this->contentType;

		if(stripos($contentType, 'text/') === 0 || in_array($contentType, array('application/json', 'application/xml')))
		{
			$contentType .= '; charset=' . $this->charset;
		}

		header('Content-Type: ' . $contentType);

		// Send other headers

		foreach($this->headers as $name => $value)
		{
			header($name . ': ' . $value);
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
	 * Returns a stream container.
	 * 
	 * @access  public
	 * @param   \Closure                    $stream  Stream
	 * @return  \mako\http\StreamContainer
	 */

	public function stream(Closure $stream)
	{
		return new StreamContainer($stream);
	}

	/**
	 * Redirects to another location.
	 *
	 * @access  public
	 * @param   string  $location    (optional) Location
	 * @param   int     $statusCode  (optional) HTTP status code
	 */
	
	public function redirect($location = '', $statusCode = 302)
	{
		$this->status($statusCode);

		if(strpos($location, '://') === false)
		{
			$location = URL::to($location);
		}
		
		$this->header('Location', $location);

		$this->sendHeaders();
		
		exit();
	}

	/**
	 * Redirects the user back to the previous page.
	 * 
	 * @access  public
	 * @param   int     $statusCode  (optional) HTTP status code
	 */

	public function back($statusCode = 302)
	{
		$this->redirect($this->request->referer(), $statusCode);
	}
	
	/**
	 * Send output to browser.
	 *
	 * @access  public
	 */
	
	public function send()
	{
		$streamResponse = $this->body instanceof StreamContainer;

		if(!$streamResponse)
		{
			// Pass output through filters

			foreach($this->outputFilters as $outputFilter)
			{
				$this->body = $outputFilter($this->body);
			}

			// Check ETag

			if($this->responseCache === true)
			{
				$hash = '"' . sha1($this->body) . '"';

				$this->header('ETag', $hash);

				if($this->request->header('if-none-match') === $hash)
				{
					$this->status(304);

					$this->sendHeaders();

					return;
				}
			}
		}

		if($streamResponse)
		{
			// This is a stream response so we'll just send the headers
			// and start flushing the stream

			$this->sendHeaders();

			$this->body->flow();
		}
		else
		{
			// This is a normal response so we'll have to include the
			// content length header

			if(ob_get_level() === 0)
			{
				// Make sure that there's an output buffer

				ob_start();
			}

			if($this->outputCompression)
			{
				// Start a compressed buffer

				ob_start('ob_gzhandler');
			}

			echo $this->body;

			if($this->outputCompression)
			{
				// Flush the compressed buffer so we can get the compress content length
				// when setting the content-length header

				ob_end_flush();
			}

			$this->header('content-length', ob_get_length());

			$this->sendHeaders();
		}
	}
}

/** -------------------- End of file -------------------- **/