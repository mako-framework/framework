<?php

namespace mako;

use \mako\URL;
use \mako\I18n;
use \mako\Config;
use \mako\Response;
use \mako\request\Router;
use \mako\request\RequestException;
use \RuntimeException;
use \ReflectionClass;

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
	 * Is this the main request?
	 *
	 * @var array
	 */

	protected $isMain = true;
	
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
	 * Controller name.
	 *
	 * @var string
	 */

	protected $controller;

	/**
	 * Controller action.
	 *
	 * @var string
	 */

	protected $action;

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
	 * Request language.
	 * 
	 * @var string
	 */

	protected static $language;

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
		static $mainRequest = true;

		$this->route = $this->getRoute($route);

		$this->method = strtoupper($this->isMain ? 
		                ($method ?: (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) :
		                (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET'))) : ($method ?: static::$main->method()));

		if($mainRequest === true)
		{
			static::$main = $this;

			$this->requestInfo();
		}
		else
		{
			$this->isMain = false;
		}

		$mainRequest = false; // Subsequent requests will be treated as subrequests
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

	protected function getRoute($route)
	{
		if(empty($route))
		{
			if(isset($_SERVER['PATH_INFO']) && $this->isMain())
			{
				$route = $_SERVER['PATH_INFO'];
			}
			elseif(isset($_SERVER['REQUEST_URI']) && $this->isMain())
			{
				if($uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
				{
					// Removes base url from uri

					$base = parse_url(URL::base(), PHP_URL_PATH);

					if(stripos($uri, $base) === 0)
					{
						$uri = mb_substr($uri, mb_strlen($base));
					}

					// Removes "/index.php" from uri

					if(stripos($uri, '/index.php') === 0)
					{
						$uri = mb_substr($uri, 10);
					}

					$route = rawurldecode($uri);
				}
			}
		}

		$route = trim($route, '/');

		if($this->isMain())
		{
			// Redirects to the current URL without index.php if clean URLs are enabled

			if(Config::get('application.clean_urls') && isset($_SERVER['REQUEST_URI']) && stripos($_SERVER['REQUEST_URI'], 'index.php') !== false)
			{
				$path = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME);

				if(stripos(ltrim(mb_substr($_SERVER['REQUEST_URI'], mb_strlen($path)), '/'), 'index.php') === 0 && stripos($route, 'index.php.') !== 0)
				{
					Response::factory()->redirect($route, 301);
				}
			}

			// Removes the locale segment from the route
			
			foreach(Config::get('routes.languages', array()) as $key => $language)
			{
				if($route === $key || strpos($route, $key . '/') === 0)
				{
					static::$language = $key;

					I18n::language($language);

					$route = trim(mb_substr($route, mb_strlen($key)), '/');

					break;
				}
			}
		}

		return $route;
	}

	/**
	 * Returns all the request headers.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function getHeaders()
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
	 * Gets information about the request.
	 * 
	 * @access protected
	 */

	protected function requestInfo()
	{
		// Get the request headers

		static::$headers = $this->getHeaders();

		// Get the ip of the client that made the request
		
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

		static::$isAjax = (bool) (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));

		// Was the request made using HTTPS?

		static::$isSecure = (!empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)) ? true : false;
	}

	/**
	 * Executes request.
	 *
	 * @access  public
	 * @return  \mako\Response
	 */

	public function execute()
	{
		// Route the request

		$router = new Router($this);

		if($router->route() === false)
		{
			throw new RequestException(404);
		}

		$this->controller = $router->getController();
		$this->action     = $router->getAction();

		// Validate controller class

		$controllerClass = new ReflectionClass($router->getNamespace() . $this->controller);

		if($controllerClass->isSubClassOf('\mako\Controller') === false)
		{
			throw new RuntimeException(vsprintf("%s(): The controller class needs to be a subclass of mako\Controller.", array(__METHOD__)));
		}

		// Check if class is abstract

		if($controllerClass->isAbstract())
		{
			throw new RequestException(404);
		}

		// Instantiate controller

		$response = new Response();

		$controller = $controllerClass->newInstance($this, $response);

		// Prefix controller action

		if($controller::RESTFUL === true)
		{
			$action = strtolower($this->method()) . '_' . $this->action;
		}
		else
		{
			$action = 'action_' . $this->action;
		}

		// Check that action exists

		if($controllerClass->hasMethod($action) === false)
		{
			if($controller::RESTFUL === true)
			{
				$requestMethods = array('get', 'post', 'put', 'delete', 'patch');

				foreach($requestMethods as $requestMethod)
				{
					if($controllerClass->hasMethod($requestMethod . '_' . $this->action))
					{
						throw new RequestException(405); // Only throw 405 if the controller has an action that can respond to the requested route
					}
				}
			}

			throw new RequestException(404);
		}

		$controllerAction = $controllerClass->getMethod($action);
		$actionArguments  = $router->getActionArguments();
		
		// Check if number of arguments match
		
		if(count($actionArguments) < $controllerAction->getNumberOfRequiredParameters() || count($actionArguments) > $controllerAction->getNumberOfParameters())
		{
			throw new RequestException(404);
		}
		
		// Run pre-action method

		$controller->before();
		
		// Run action

		$response->body($controllerAction->invokeArgs($controller, $actionArguments));

		// Run post-action method

		$controller->after();

		return $response;
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
	 * Is this the main request?
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isMain()
	{
		return $this->isMain;
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
	 * Returns the name of the requested controller.
	 *
	 * @access  public
	 * @return  string
	 */

	public function controller()
	{
		return $this->controller;
	}

	/**
	 * Returns the name of the requested action.
	 *
	 * @access  public
	 * @return  string
	 */

	public function action()
	{
		return $this->action;
	}

	/**
	 * Which request method was used?
	 *
	 * @access  public
	 * @return  string
	 */

	public function method()
	{
		return $this->method;
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
	 * From where did the request originate?
	 *
	 * @access  public
	 * @param   string  $default  (optional) Value to return if no referer is set
	 * @return  string
	 */

	public static function referer($default = '')
	{
		return static::header('referer', $default);
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
	 * Is this an Ajax request?
	 *
	 * @access  public
	 * @return  boolean
	 */

	public static function isAjax()
	{
		return static::$isAjax;
	}

	/**
	 * Was the reqeust made using HTTPS?
	 *
	 * @access  public
	 * @return  boolean
	 */

	public static function isSecure()
	{
		return static::$isSecure;
	}
}

/** -------------------- End of file -------------------- **/