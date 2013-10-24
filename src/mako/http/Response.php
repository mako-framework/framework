<?php

namespace mako\http;

use \Closure;
use \mako\core\Config;
use \mako\security\MAC;
use \mako\http\Request;
use \mako\http\routing\URL;

/**
 * Mako response class.
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
	 * Holds the response body.
	 *
	 * @var string
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

	protected $responseHeaders = array();

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
	 * @var \Closure
	 */
	
	protected $outputFilters = array();
	
	/**
	 * List of HTTP status codes.
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
	 * @access  protected
	 * @param   string     $body  (optional) Response body
	 */
	
	public function __construct($body = null)
	{
		if($body !== null)
		{
			$this->body($body);
		}

		$config = Config::get('application');

		$this->outputCompression = $config['compress_output'];
		$this->responseCache     = $config['response_cache'];
	}

	/**
	 * Factory method making method chaining possible right off the bat.
	 *
	 * @access  public
	 * @param   string          $body  (optional) Response body
	 * @return  \mako\Response
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
	 * @param   string               $body  Response body
	 * @return  \mako\http\Response
	 */

	public function body($body)
	{
		$this->body = (string) $body;

		return $this;
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
		$this->responseHeaders[$name] = $value;

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
		$this->responseHeaders = array();

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
		$this->redirect(Request::main()->referer(), $statusCode);
	}

	/**
	 * Sends response headers.
	 * 
	 * @access  protected
	 * @param   \mako\http\Request  $request  (optional) Main instance
	 */

	protected function sendHeaders($request = null)
	{
		$request = $request ?: Request::main();

		// Send content type header

		$contentType = $this->contentType;

		if(stripos($contentType, 'text/') === 0 || in_array($contentType, array('application/json', 'application/xml')))
		{
			$contentType .= '; charset=' . $this->charset;
		}

		header('Content-Type: ' . $contentType);

		// Send cookie headers

		foreach($this->cookies as $cookie)
		{
			setcookie($cookie['name'], $cookie['value'], $cookie['ttl'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
		}

		// Send status header

		if($request->server('FCGI_SERVER_VERSION', false) !== false)
		{
			$protocol = 'Status:';
		}
		else
		{
			$protocol = $request->server('SERVER_PROTOCOL', 'HTTP/1.1');
		}

		header($protocol . ' ' . $this->statusCode . ' ' . $this->statusCodes[$this->statusCode]);

		// Send other headers

		foreach($this->responseHeaders as $name => $value)
		{
			header($name . ': ' . $value);
		}
	}
	
	/**
	 * Send output to browser.
	 *
	 * @access  public
	 * @param   int     $statusCode  (optional) HTTP status code
	 */
	
	public function send($statusCode = null)
	{
		if($statusCode !== null)
		{
			$this->status($statusCode);
		}

		$request = Request::main();

		// Send response headers

		$this->sendHeaders($request);

		// Print output to browser (if there is any)

		if($this->body !== '')
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

				header('ETag: ' . $hash);

				if($request->header('if-none-match') === $hash)
				{
					$this->status(304);

					return; // Don't send any output
				}
			}

			// Compress output

			if($this->outputCompression)
			{
				ob_start('ob_gzhandler');
			}

			// Send output

			echo $this->body;
		}
	}

	/**
	 * Method that magically converts the response object into a string.
	 *
	 * @access  public
	 * @return  string
	 */

	public function __toString()
	{
		return $this->body;
	}
}

/** -------------------- End of file -------------------- **/