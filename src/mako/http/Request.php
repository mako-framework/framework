<?php

namespace mako\http;

use \mako\i18n\I18n;
use \mako\core\Config;
use \mako\http\Input;
use \mako\http\Response;
use \mako\http\RequestException;
use \mako\http\routing\URL;
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
	 * @var \mako\http\Request
	 */

	protected static $main;

	/**
	 * Request input.
	 * 
	 * @var \mako\http\Input
	 */

	protected $input;

	/**
	 * Request headers.
	 * 
	 * @var array
	 */

	protected $headers = array();
	
	/**
	 * Ip address of the client that made the request.
	 *
	 * @var string
	 */
	
	protected $ip = '127.0.0.1';

	/**
	 * Is this an Ajax request?
	 *
	 * @var boolean
	 */

	protected $isAjax;

	/**
	 * Was the request made using HTTPS?
	 *
	 * @var boolean
	 */

	protected $isSecure;

	/**
	 * Request language.
	 * 
	 * @var string
	 */

	protected $language;
	
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
	 * The actual request method that was used.
	 * 
	 * @var string
	 */

	protected $realMethod;

	/**
	 * Matched route.
	 * 
	 * @var \mako\http\routing\Route
	 */

	protected $matchedRoute;

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
	 * @param   string  $route   (optional) Request route
	 * @param   string  $method  (optional) Request method
	 * @param   array   $get     (optional) GET parameters
	 * @param   array   $post    (optional) POST parameters
	 * @param   array   $cookies (optional) Cookies
	 * @param   array   $files   (optional) Files
	 * @param   array   server   (optional) Server info
	 * @param   string  $body    (optional) Request body
	 */

	public function __construct($route = null, $method = null, array $get = array(), array $post = array(), array $cookies = array(), array $files = array(), array $server = array(), $body = null)
	{
		static $isMainRequest = true;

		// Referece to the main request

		if($isMainRequest)
		{
			static::$main = $this;
		}

		// Create request input object

		$this->input = new Input($this, $get, $post, $cookies, $files, $server, $body);

		// Collect the request headers

		$this->headers = $this->collectHeaders();

		// Collect request info

		$this->collectRequestInfo();

		// Set the request route and method

		$this->route = ($isMainRequest && empty($route)) ? $this->getRoute() : $route;

		$this->method = ($isMainRequest && empty($method)) ? $this->detectMethod() : ($method ?: static::$main->method());

		// Subsequent requests will be treated as subrequests

		$isMainRequest = false;
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

		$server = $this->input->server();

		if(isset($server['PATH_INFO']))
		{
			$route = $server['PATH_INFO'];
		}
		elseif(isset($server['REQUEST_URI']))
		{
			if($route = parse_url($server['REQUEST_URI'], PHP_URL_PATH))
			{
				// Remove base path from route

				$basePath = pathinfo($server['SCRIPT_NAME'], PATHINFO_DIRNAME);

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

		if(Config::get('application.clean_urls') && isset($server['REQUEST_URI']) && stripos($server['REQUEST_URI'], 'index.php') !== false)
		{
			$path = pathinfo($server['SCRIPT_NAME'], PATHINFO_DIRNAME);

			if(stripos(mb_substr($server['REQUEST_URI'], mb_strlen($path)), '/index.php') === 0)
			{
				Response::factory()->redirect(URL::to($route, $this->input->get(), '&'), 301);
			}
		}

		// Remove the locale segment from the route
			
		foreach(Config::get('application.languages') as $key => $language)
		{
			if($route === '/' . $key || strpos($route, '/' . $key . '/') === 0)
			{
				$this->language = $key;

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

		$server = $this->input->server();

		if(isset($server['REQUEST_METHOD']))
		{
			$method = strtoupper($server['REQUEST_METHOD']);
		}

		if($method === 'POST')
		{
			$post = $this->input->post();

			if(isset($post['REQUEST_METHOD_OVERRIDE']))
			{
				$method = $post['REQUEST_METHOD_OVERRIDE'];
			}
			elseif(isset($server['HTTP_X_HTTP_METHOD_OVERRIDE']))
			{
				$method = $server['HTTP_X_HTTP_METHOD_OVERRIDE'];
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

		foreach($this->input->server() as $key => $value)
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
		// Get the IP address of the client that made the request

		$server = $this->input->server();
		
		if(!empty($server['HTTP_X_FORWARDED_FOR']))
		{
			$ip = explode(',', $server['HTTP_X_FORWARDED_FOR']);
			
			$ip = array_pop($ip);
		}
		elseif(!empty($server['HTTP_CLIENT_IP']))
		{
			$ip = $server['HTTP_CLIENT_IP'];
		}
		elseif(!empty($server['HTTP_X_CLUSTER_CLIENT_IP']))
		{
			$ip = $server['HTTP_X_CLUSTER_CLIENT_IP'];
		}
		elseif(!empty($server['REMOTE_ADDR']))
		{
			$ip = $server['REMOTE_ADDR'];
		}
		
		if(isset($ip) && filter_var($ip, FILTER_VALIDATE_IP) !== false)
		{
			$this->ip = $ip;
		}
		
		// Is this an Ajax request?

		$this->isAjax = (isset($server['HTTP_X_REQUESTED_WITH']) && ($server['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));

		// Was the request made using HTTPS?

		$this->isSecure = (!empty($server['HTTPS']) && filter_var($server['HTTPS'], FILTER_VALIDATE_BOOLEAN)) ? true : false;

		// Get the real request method that was used

		$this->realMethod = isset($server['REQUEST_METHOD']) ? strtoupper($server['REQUEST_METHOD']) : 'GET';
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

		$this->matchedRoute = $router->route();

		if($this->method === 'OPTIONS' && $this->isMain())
		{
			return Response::factory()->header('Allow', implode(', ', $this->matchedRoute->getMethods()));
		}
		else
		{
			$this->parameters = $this->matchedRoute->getParameters();

			$dispatcher = new Dispatcher($this);

			return $dispatcher->dispatch();
		}
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
	 * Returns the input instance.
	 * 
	 * @access  public
	 * @return  \mako\http\Input
	 */

	public function input()
	{
		return $this->input;
	}

	/**
	 * Returns a request header.
	 * 
	 * @access  public
	 * @param   string  $name     Header name
	 * @param   mixed   $default  Default value
	 * @return  mixed
	 */

	public function header($name, $default = null)
	{
		$name = strtoupper(str_replace('-', '_', $name));

		return isset($this->headers[$name]) ? $this->headers[$name] : $default;
	}

	/**
	 * Returns the ip of the client that made the request.
	 *
	 * @access  public
	 * @return  string
	 */
	
	public function ip()
	{
		return $this->ip;
	}

	/**
	 * Returns TRUE if the request was made using Ajax and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isAjax()
	{
		return $this->isAjax;
	}

	/**
	 * Returns TRUE if the request made using HTTPS and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isSecure()
	{
		return $this->isSecure;
	}

	/**
	 * Returns the request language.
	 *
	 * @access  public
	 * @return  string
	 */

	public function language()
	{
		return $this->language;
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
	 * Returns the real request method that was used.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function realMethod()
	{
		return $this->realMethod;
	}

	/**
	 * Returns TRUE if the request method has been faked and FALSE if not.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function isFaked()
	{
		return $this->realMethod !== $this->method;
	}

	/**
	 * Returns the matched route.
	 * 
	 * @access  public
	 * @return  \mako\http\routing\Route
	 */

	public function matchedRoute()
	{
		return $this->matchedRoute;
	}

	/**
	 * Returns a request parameter.
	 * 
	 * @access  public
	 * @param   string  $key      Parameter name
	 * @param   mixed   $default  (optional) Default value
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

	public function username()
	{
		return $this->input->server('PHP_AUTH_USER');
	}

	/**
	 * Returns the basic HTTP authentication password or NULL.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function password()
	{
		return $this->input->server('PHP_AUTH_PW');
	}

	/**
	 * Returns the referer.
	 *
	 * @access  public
	 * @param   string  $default  (optional) Value to return if no referer is set
	 * @return  string
	 */

	public function referer($default = '')
	{
		return $this->header('referer', $default);
	}
}

/** -------------------- End of file -------------------- **/