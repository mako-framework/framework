<?php

namespace mako\http;

use \mako\i18n\I18n;
use \mako\Config;
use \mako\http\Response;
use \mako\http\RequestException;
use \mako\http\routing\Router;
use \mako\http\routing\Dispatcher;

/**
 * Executes requets.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Request
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Holds the main request instance.
	 * 
	 * @var \mako\Request
	 */

	protected static $main;

	/**
	 * Request headers.
	 * 
	 * @var array
	 */

	protected static $headers = array();
	
	/**
	 * Ip address of the client that made the request.
	 *
	 * @var string
	 */
	
	protected static $ip = '127.0.0.1';

	/**
	 * Is this an Ajax request?
	 *
	 * @var boolean
	 */

	protected static $isAjax;

	/**
	 * Was the request made using HTTPS?
	 *
	 * @var boolean
	 */

	protected static $isSecure;

	/**
	 * The actual request method that was used.
	 * 
	 * @var string
	 */

	protected static $realMethod;

	/**
	 * Request language.
	 * 
	 * @var string
	 */

	protected static $language;
	
	/**
	 * Holds the route passed to the constructor.
	 *
	 * @var string
	 */

	protected $route;

	/**
	 * Which request method was used?
	 *
	 * @var string
	 */

	protected $method;

	/**
	 * Request parameters.
	 * 
	 * @var array
	 */

	protected $parameters = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $route   (optional) URL segments
	 * @param   string  $method  (optional) Request method
	 */

	public function __construct($route = null, $method = null)
	{
		static $isMainRequest = true; // The first request will be treated as the main request

		if($isMainRequest)
		{
			static::$main = $this;

			$this->collectRequestInfo();
		}

		$this->route = ($isMainRequest && empty($route)) ? $this->getRoute() : $route;

		$this->method = ($isMainRequest && empty($method)) ? $this->detectMethod() : ($method ?: static::$main->method());

		$isMainRequest = false; // Subsequent requests will be treated as subrequests
	}

	/**
	 * Factory method making method chaining possible right off the bat.
	 *
	 * @access  public
	 * @param   string         $route   (optional) URL segments
	 * @param   string         $method  (optional) Request method
	 * @return  \mako\Request
	 */

	public static function factory($route = null, $method = null)
	{
		return new static($route, $method);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the requested route.
	 *
	 * @access  protected
	 * @param   string     $route  The requested route
	 * @return  string
	 */

	protected function getRoute()
	{
		$route = '/';

		if(isset($_SERVER['PATH_INFO']))
		{
			$route = $_SERVER['PATH_INFO'];
		}
		elseif(isset($_SERVER['REQUEST_URI']))
		{
			if($route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
			{
				// Remove base path from route

				$basePath = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME);

				if(stripos($route, $basePath) === 0)
				{
					$route = mb_substr($route, mb_strlen($basePath));
				}

				// Remove "/index.php" from route

				if(stripos($route, '/index.php') === 0)
				{
					$route = mb_substr($route, 10);
				}

				$route = rawurldecode($route);
			}
		}

		// Redirect to the current URL without "index.php" if clean URLs are enabled

		if(Config::get('application.clean_urls') && isset($_SERVER['REQUEST_URI']) && stripos($_SERVER['REQUEST_URI'], 'index.php') !== false)
		{
			$path = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME);

			if(stripos(mb_substr($_SERVER['REQUEST_URI'], mb_strlen($path)), '/index.php') === 0)
			{
				Response::factory()->redirect($route, 301);
			}
		}

		// Remove the locale segment from the route
			
		foreach(Config::get('application.languages') as $key => $language)
		{
			if($route === '/' . $key || strpos($route, '/' . $key . '/') === 0)
			{
				static::$language = $key;

				I18n::language($language);

				$route = '/' . ltrim(mb_substr($route, (mb_strlen($key) + 1)), '/');

				break;
			}
		}

		return $route;
	}

	/**
	 * Detects the request method.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function detectMethod()
	{
		$method = 'GET';

		if(isset($_SERVER['REQUEST_METHOD']))
		{
			$method = strtoupper($_SERVER['REQUEST_METHOD']);
		}

		if($method === 'POST')
		{
			if(isset($_POST['_request_method_']))
			{
				$method = $_POST['_request_method_'];
			}
			elseif(isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']))
			{
				$method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
			}
		}

		return strtoupper($method);
	}

	/**
	 * Returns all the request headers.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function collectHeaders()
	{
		$headers = array();

		foreach($_SERVER as $key => $value)
		{
			if(strpos($key, 'HTTP_') === 0)
			{
				$headers[substr($key, 5)] = $value;
			}
			elseif(in_array($key, array('CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE')))
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
		// Collect the request headers

		static::$headers = $this->collectHeaders();

		// Get the IP address of the client that made the request
		
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			
			$ip = array_pop($ip);
		}
		elseif(!empty($_SERVER['HTTP_CLIENT_IP']))
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif(!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
		{
			$ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		}
		elseif(!empty($_SERVER['REMOTE_ADDR']))
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		if(isset($ip) && filter_var($ip, FILTER_VALIDATE_IP) !== false)
		{
			static::$ip = $ip;
		}
		
		// Is this an Ajax request?

		static::$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));

		// Was the request made using HTTPS?

		static::$isSecure = (!empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)) ? true : false;

		// Get the real request method that was used

		static::$realMethod = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
	}

	/**
	 * Executes the request.
	 *
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function execute()
	{
		$router = new Router($this);

		$route = $router->route();

		$this->parameters = $route->getParameters();

		$dispatcher = new Dispatcher($this, $route);

		return $dispatcher->dispatch();
	}

	/**
	 * Returns the main request.
	 * 
	 * @access  public
	 * @return  \mako\Request
	 */

	public static function main()
	{
		return static::$main;
	}

	/**
	 * Returns TRUE if this is the main request and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isMain()
	{
		return (static::$main === $this);
	}

	/**
	 * Returns a request header.
	 * 
	 * @access  public
	 * @param   string  $name     Header name
	 * @param   mixed   $default  Default value
	 * @return  mixed
	 */

	public static function header($name, $default = null)
	{
		$name = strtoupper(str_replace('-', '_', $name));

		return isset(static::$headers[$name]) ? static::$headers[$name] : $default;
	}

	/**
	 * Returns the ip of the client that made the request.
	 *
	 * @access  public
	 * @return  string
	 */
	
	public static function ip()
	{
		return static::$ip;
	}

	/**
	 * Returns TRUE if the request was made using Ajax and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public static function isAjax()
	{
		return static::$isAjax;
	}

	/**
	 * Returns TRUE if the request made using HTTPS and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public static function isSecure()
	{
		return static::$isSecure;
	}

	/**
	 * Returns the real request method that was used.
	 * 
	 * @access  public
	 * @return  string
	 */

	public static function realMethod()
	{
		return static::$realMethod;
	}

	/**
	 * Returns the request language.
	 *
	 * @access  public
	 * @return  string
	 */

	public static function language()
	{
		return static::$language;
	}

	/**
	 * Returns the route of the request.
	 *
	 * @access  public
	 * @return  string
	 */

	public function route()
	{
		return $this->route;
	}

	/**
	 * Returns the request method that was used.
	 *
	 * @access  public
	 * @return  string
	 */

	public function method()
	{
		return $this->method;
	}

	/**
	 * Returns a request parameter.
	 * 
	 * @access  public
	 * @param   string  $key      Parameter name
	 * @param   mixed   $default  Default value
	 * @return  mixed
	 */

	public function param($key, $default = null)
	{
		return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
	}

	/**
	 * Returns the basic HTTP authentication username or NULL.
	 * 
	 * @access  public
	 * @return  string
	 */

	public static function username()
	{
		return isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
	}

	/**
	 * Returns the basic HTTP authentication password or NULL.
	 * 
	 * @access  public
	 * @return  string
	 */

	public static function password()
	{
		return isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;
	}

	/**
	 * Returns the referer.
	 *
	 * @access  public
	 * @param   string  $default  (optional) Value to return if no referer is set
	 * @return  string
	 */

	public static function referer($default = '')
	{
		return static::header('referer', $default);
	}
}

/** -------------------- End of file -------------------- **/