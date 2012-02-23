<?php

namespace mako;

use \mako\Config;
use \mako\Response;
use \RuntimeException;
use \ReflectionClass;

class RequestException extends RuntimeException{}

/**
* Routes the request and executes the controller.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Request
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------
	
	/**
	* Holds the route passed to the constructor.
	*
	* @var string
	*/

	protected $route;

	/**
	* Holds the route to the main request.
	*
	* @var string
	*/

	protected static $mainRoute;
	
	/**
	* Default route.
	*
	* @var string
	*/
	
	protected $defaultRoute;
	
	/**
	* Custom routes.
	*
	* @var array
	*/
	
	protected $customRoutes;

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
	* Which request method was used?
	*
	* @var string
	*/

	protected static $method;

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
	* Array holding the arguments of the action method.
	*
	* @var array
	*/

	protected $actionArgs;

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
	* Namespace of the controller class.
	*
	* @var string
	*/

	protected $namespace;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	* @param   string  (optional) URL segments
	*/

	public function __construct($route = null)
	{
		$this->route = $route;
		
		$config = Config::get('routes');
		
		$this->defaultRoute = $config['default_route'];
		$this->customRoutes = $config['custom_routes'];
		
		$this->namespace = '\\' . MAKO_APPLICATION_NAME . '\controllers\\';
		
		static $mainRequest = true;

		if($mainRequest === true)
		{
			// Get the ip of the client that made the request
			
			if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			{
				$ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				
				$ip = array_pop($ip);
			}
			else if(!empty($_SERVER['HTTP_CLIENT_IP']))
			{
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			else if(!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
			{
				$ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
			}
			else if(!empty($_SERVER['REMOTE_ADDR']))
			{
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			
			if(isset($ip) && filter_var($ip, FILTER_VALIDATE_IP) !== false)
			{
				static::$ip = $ip;
			}

			// From where did the request originate?

			static::$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

			// Which request method was used?

			static::$method = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) : 
			                  (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
			
			// Is this an Ajax request?

			static::$isAjax = (bool) (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));

			// Was the request made using HTTPS?

			static::$secure = (!empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)) ? true : false;
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
	* @param   string        (optional) URL segments
	* @return  mako\Request
	*/

	public static function factory($route = null)
	{
		return new static($route);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Routes the request to the appropriate controller action.
	*
	* @access  protected
	* @return  boolean
	*/

	protected function router()
	{
		// Set root path
		
		$controllerPath = $controllerRootPath = MAKO_APPLICATION . '/controllers/';

		// Get the route

		$route = '';

		if($this->route !== null)
		{
			$route = $this->route;
		}
		else if(isset($_SERVER['PATH_INFO']) && $this->isMain())
		{
			$route = $_SERVER['PATH_INFO'];
		}
		else if(isset($_SERVER['PHP_SELF']) && $this->isMain())
		{
			$route = mb_substr($_SERVER['PHP_SELF'], mb_strlen($_SERVER['SCRIPT_NAME']));
		}

		$route = trim($route, '/');

		if($this->isMain())
		{
			static::$mainRoute = $route;
		}

		if($route === '')
		{
			$route = trim($this->defaultRoute, '/');
		}

		// Remap custom routes

		if(count($this->customRoutes) > 0)
		{
			foreach($this->customRoutes as $pattern => $realRoute)
			{		
				if(preg_match('#^' . $pattern . '$#iu', $route) === 1)
				{
					if(strpos($realRoute, '$') !== false)
					{
						$realRoute = preg_replace('#^' . $pattern . '$#iu', $realRoute, $route);
					}

					$route = trim($realRoute, '/');

					break;
				}
			}
		}

		// Get the URL segments

		$segments = explode('/', $route, 100);

		// Route the request

		foreach($segments as $segment)
		{
			$path = $controllerPath . $segment;

			if(is_dir($path))
			{
				// Just a directory - Jump to next iteration

				$controllerPath  .= $segment . '/';

				$this->namespace .= $segment . '\\';

				array_shift($segments);

				continue;
			}
			else if(is_file($path . '.php'))
			{
				// We have found our controller - Exit loop

				$this->controller = $segment;

				array_shift($segments);

				break;
			}
			else
			{
				// No directory or controller - Stop routing

				return false;
			}
		}
		
		if(empty($this->controller))
		{
			$this->controller = 'index'; // default controller
		}

		// Get the action we want to execute

		$this->action = array_shift($segments);

		if($this->action === null)
		{
			$this->action = 'index';
		}

		// Remaining segments are passed as parameters to the action

		$this->actionArgs = $segments;

		// Check if file exists

		if(file_exists($controllerPath . $this->controller . '.php') === false)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	* Executes the controller and action found by the route method.
	*
	* @access  public
	* @return  mako\Response
	*/

	public function execute()
	{
		// Route request

		if($this->router() === false)
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

		if($controller instanceof \mako\controller\Rest)
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
		
		// Check if number of parameters match
		
		if(count($this->actionArgs) < $controllerAction->getNumberOfRequiredParameters() || count($this->actionArgs) > $controllerAction->getNumberOfParameters())
		{
			throw new RequestException(404);
		}
		
		// Run pre-action method

		$controller->before();
		
		// Run action

		$response->body($controllerAction->invokeArgs($controller, $this->actionArgs));

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
	* @param   string  (optional) Value to return if no referer is set
	* @return  string
	*/

	public static function referer($default = '')
	{
		return empty(static::$referer) ? $default : static::$referer;
	}

	/**
	* Returns the route of the main request.
	*
	* @access  public
	* @return  string
	*/

	public static function route()
	{
		return static::$mainRoute;
	}

	/**
	* Which request method was used?
	*
	* @access  public
	* @return  string
	*/

	public static function method()
	{
		return static::$method;
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