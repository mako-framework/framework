<?php

namespace mako;

use \Closure;
use \mako\Config;
use \mako\Request;
use \mako\DebugToolbar;

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
	 * Output filter.
	 *
	 * @var Closure
	 */
	
	protected $outputFilter;

	/**
	 * Response headers.
	 * 
	 * @var array
	 */

	protected $responseHeaders = array();
	
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
	 * @param   string    $body  Response body
	 */

	public function body($body)
	{
		$this->body = (string) $body;
	}
	
	/**
	 * Adds output filter that all output will be passed through before being sent.
	 *
	 * @access  public
	 * @param   \Closure  $filter  Closure used to filter output
	 */
	
	public function filter(Closure $filter)
	{
		$this->outputFilter = $filter;
	}

	/**
	 * Sets a response header.
	 * 
	 * @access  public
	 * @param   string  $name   Header name
	 * @param   string  $value  Header value
	 */

	public function header($name, $value)
	{
		$this->responseHeaders[$name] = $value;
	}

	/**
	 * Clear the response headers.
	 * 
	 * @access  public
	 */

	public function clearHeaders()
	{
		$this->responseHeaders = array();
	}

	/**
	 * Sets the response content type.
	 * 
	 * @access  public
	 * @param   string  $contentType  Content type
	 * @param   string  $charset      (optional) Charset
	 */

	public function type($contentType, $charset = null)
	{
		$this->contentType = $contentType;

		if($charset !== null)
		{
			$this->charset = $charset;
		}
	}

	/**
	 * Sets the response charset.
	 * 
	 * @access  public
	 * @param   string  $charset  Charset
	 */

	public function charset($charset)
	{
		$this->charset = $charset;
	}

	/**
	 * Sends response headers.
	 * 
	 * @access  protected
	 */

	protected function sendHeaders()
	{
		// Send content type header

		$contentType = $this->contentType;

		if(stripos($contentType, 'text/') === 0 || in_array($contentType, array('application/json', 'application/xml')))
		{
			$contentType .= '; charset=' . $this->charset;
		}

		header('Content-Type: ' . $contentType);

		// Send other headers

		foreach($this->responseHeaders as $name => $value)
		{
			header($name . ': ' . $value);
		}
	}

	/**
	 * Sends HTTP status header.
	 *
	 * @access  public
	 * @param   int     HTTP status code
	 */
	
	public function status($statusCode)
	{
		if(isset($this->statusCodes[$statusCode]))
		{
			if(isset($_SERVER['FCGI_SERVER_VERSION']))
			{
				$protocol = 'Status:';
			}
			else
			{
				$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
			}
			
			
			header($protocol . ' ' . $statusCode . ' '. $this->statusCodes[$statusCode]);
		}
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
		
		header('Location: ' . $location);
		
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
		$this->redirect(Request::referer(), $statusCode);
	}

	/**
	 * Enables ETag response cache.
	 *
	 * @access  public
	 */

	public function cache()
	{
		$this->responseCache = true;
	}

	/**
	 * Disables ETag response cache.
	 *
	 * @access  public
	 */

	public function disableCaching()
	{
		$this->responseCache = false;
	}

	/**
	 * Enables output compression.
	 * 
	 * @access  public
	 */

	public function compress()
	{
		$this->outputCompression = true;
	}

	/**
	 * Disables output compression.
	 * 
	 * @access  public
	 */

	public function disableCompression()
	{
		$this->outputCompression = false;
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

		// Send response headers

		$this->sendHeaders();

		// Print output to browser (if there is any)

		if($this->body !== '')
		{	
			// Pass output through filter
			
			if(!empty($this->outputFilter))
			{
				$filter = $this->outputFilter;

				$this->body = $filter($this->body);
			}

			// Add debug toolbar?

			if(Config::get('application.debug_toolbar') === true && Request::isAjax() === false)
			{
				$this->body = str_replace('</body>', DebugToolbar::render() . '</body>', $this->body);
			}

			// Check ETag

			if($this->responseCache === true)
			{
				$hash = '"' . sha1($this->body) . '"';

				header('ETag: ' . $hash);

				if(Request::header('if-none-match') === $hash)
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