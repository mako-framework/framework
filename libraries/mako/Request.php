<?php

namespace mako;

use \mako\URL;
use \mako\I18n;
use \mako\Config;
use \mako\Response;
use \mako\request\Router;
use \RuntimeException;
use \ReflectionClass;

class RequestException extends RuntimeException{}

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
	 * Holds the main request instance.
	 * 
	 * @var mako\Request
	 */

	protected static $main;

	/**
	 * Request language.
	 * 
	 * @var string
	 */

	protected static $language;

	/**
	 * Is this the main request?
	 *
	 * @var array
	 */

	protected $isMain = true;
	
	/**
	 * Ip address of the cilent that made the request.
	 *
	 * @var string
	 */
	
	protected static $ip = '127.0.0.1';

	/**
	 * From where did the request originate?
	 *
	 * @var string
	 */

	protected static $referer;

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

	protected static $secure;

	/**
	 * Namespace of the controller class.
	 *
	 * @var string
	 */

	protected $namespace;

	/**
	 * Name of the controller.
	 *
	 * @var string
	 */

	protected $controller;

	/**
	 * Name of the action.
	 *
	 * @var string
	 */

	protected $action;

	/**
	 * Array holding the arguments of the action method.
	 *
	 * @var array
	 */

	protected $actionArguments;

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
		$this->route = $route;

		$this->method = strtoupper($this->isMain ? 
		                ($method ?: (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) :
		                (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET'))) : ($method ?: static::$main->method()));

		static $mainRequest = true;

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
	 * @param   string        $route   (optional) URL segments
	 * @param   string        $method  (optional) Request method
	 * @return  mako\Request
	 */

	public static function factory($route = null, $method = null)
	{
		return new static($route, $method);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sets the controller namespace
	 * 
	 * @access  public
	 * @param   string  $namespace  Controller namespace
	 */

	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * Sets the controller class.
	 * 
	 * @access  public
	 * @param   string  $controller  Controller class
	 */

	public function setController($controller)
	{
		$this->controller = $controller;
	}

	/**
	 * Sets the controller action.
	 * 
	 * @access  public
	 * @param   string  $action  Controller action
	 */

	public function setAction($action)
	{
		$this->action = $action;
	}

	/**
	 * Sets the controller action arguments.
	 * 
	 * @access public
	 * @param  array   $arguments  Controller action arguments
	 */

	public function setActionArguments(array $arguments)
	{
		$this->actionArguments = $arguments;
	}

	/**
	 * Gets information about the request.
	 * 
	 * @access protected
	 */

	protected function requestInfo()
	{
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

		// From where did the request originate?

		static::$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		
		// Is this an Ajax request?

		static::$isAjax = (bool) (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));

		// Was the request made using HTTPS?

		static::$secure = (!empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)) ? true : false;
	}

	/**
	 * Returns the requested route.
	 *
	 * @access  protected
	 * @return  string
	 */

	protected function getRoute()
	{
		$route = '';

		if($this->route !== null)
		{
			$route = $this->route;
		}
		elseif(isset($_SERVER['PATH_INFO']) && $this->isMain())
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

			$this->route = $route;
		}

		return $route;
	}

	/**
	 * Executes request.
	 *
	 * @access  public
	 * @return  mako\Response
	 */

	public function execute()
	{
		// Route the request

		$router = new Router($this, $this->getRoute());

		if($router->route() === false)
		{
			throw new RequestException(404);
		}

		// Validate controller class

		$controllerClass = new ReflectionClass($this->namespace . $this->controller);

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
			throw new RequestException(404);
		}

		$controllerAction = $controllerClass->getMethod($action);
		
		// Check if number of arguments match
		
		if(count($this->actionArguments) < $controllerAction->getNumberOfRequiredParameters() || count($this->actionArguments) > $controllerAction->getNumberOfParameters())
		{
			throw new RequestException(404);
		}
		
		// Run pre-action method

		$controller->before();
		
		// Run action

		$response->body($controllerAction->invokeArgs($controller, $this->actionArguments));

		// Run post-action method

		$controller->after();

		return $response;
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
	 * Returns the main request.
	 * 
	 * @access  public
	 * @return  mako\Request
	 */

	public static function main()
	{
		return static::$main;
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
		return empty(static::$referer) ? $default : static::$referer;
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
		return static::$secure;
	}
}

/** -------------------- End of file --------------------**/