<?php

namespace mako
{
	use \mako\Mako;
	use \mako\View;
	use \mako\Response;
	use \Closure;
	use \RuntimeException;
	use \ReflectionClass;
	use \ReflectionException;
	use \BadMethodCallException;
	
	/**
	* Routes the request and executes the controller.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Request
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* Holds the route passed to the constructor.
		*/

		protected $route;
		
		/**
		* Default route.
		*/
		
		protected $defaultRoute;
		
		/**
		* Custom routes.
		*/
		
		protected $customRoutes;

		/**
		* Was the request made from the CLI?
		*/

		protected static $isCli;

		/**
		* Is this a subrequest?
		*/

		protected $isSubrequest = false;
		
		/**
		* Ip address of the cilent that made the request.
		*/
		
		protected static $ip = '127.0.0.1';

		/**
		* From where did the request originate?
		*/

		protected static $referer;

		/**
		* Which request method was used?
		*/

		protected static $method;

		/**
		* Is this an Ajax request?
		*/

		protected static $isAjax;

		/**
		* Was the request made using HTTPS?
		*/

		protected static $secure;

		/**
		* Array holding the arguments of the action method.
		*/

		protected $actionArgs;

		/**
		* Name of the controller.
		*/

		protected $controller;

		/**
		* Name of the action.
		*/

		protected $action;

		/**
		* Namespace of the controller class.
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
			
			$config = Mako::config('request');
			
			$this->defaultRoute = $config['default_route'];
			$this->customRoutes = $config['custom_routes'];
			
			$this->namespace = '\\' . MAKO_APPLICATION_NAME . '\controllers\\';
			
			static $mainRequest = true;

			if($mainRequest === true)
			{
				// Was the request made from the CLI?

				static::$isCli = (PHP_SAPI === 'cli');

				if(static::isCli() === false)
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
			}
			else
			{
				$this->isSubrequest = true;
			}

			$mainRequest = false; // Subsequent requests will be treated as subrequests
		}

		/**
		* Factory method making method chaining possible right off the bat.
		*
		* @access  public
		* @param   string   (optional) URL segments
		* @return  Request
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

		protected function route()
		{
			// Set root path
			
			$controllerPath = $controllerRootPath = MAKO_APPLICATION . '/controllers/';

			// Get the route

			$route = '';

			if(static::isCli() && $this->isSubrequest === false)
			{
				if(!empty($_SERVER['argv'][1]))
				{
					$route = $_SERVER['argv'][1];
				}
			}
			else
			{
				if($this->route !== null)
				{
					$route = $this->route;
				}
				else if(isset($_SERVER['PATH_INFO']))
				{
					$route = $_SERVER['PATH_INFO'];
				}
				else if(isset($_SERVER['PHP_SELF']))
				{
					$route = mb_substr($_SERVER['PHP_SELF'], mb_strlen($_SERVER['SCRIPT_NAME']));
				}
			}

			$route = trim($route, '/');

			if($route === '')
			{
				$route = trim($this->defaultRoute, '/');
			}

			// Remap custom routes

			if(count($this->customRoutes) > 0)
			{
				foreach($this->customRoutes as $pattern => $realRoute)
				{		
					if(preg_match('#^' . $pattern . '$#iu', $route, $matches) === 1)
					{
						if($realRoute instanceof Closure)
						{
							call_user_func_array($realRoute, $matches);

							exit();
						}
						else
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

			$this->action = array_shift($segments);

			if($this->action === null)
			{
				$this->action = '_index'; // default action
			}

			// Remaining segments are passed as parameters to the action

			$this->actionArgs = $segments;

			// Check for directory traversal

			if(mb_substr(realpath($controllerPath), 0, mb_strlen(realpath($controllerRootPath))) !== realpath($controllerRootPath))
			{
				throw new RuntimeException(__CLASS__ . ": Directory traversal detected.");
			}

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
		*/

		public function execute()
		{
			// Route request

			if($this->route() === false)
			{
				$this->notFound();
			}

			// Validate controller class

			$controllerClass = new ReflectionClass($this->namespace . $this->controller);

			if($controllerClass->isSubClassOf('\mako\Controller') === false)
			{
				throw new RuntimeException(__CLASS__ . ": The controller class needs to be a subclass of mako\Controller.");
			}

			// Check if class is abstract

			if($controllerClass->isAbstract())
			{
				$this->notFound();
			}

			// Instantiate controller

			$controller = $controllerClass->newInstance($this, Response::instance());

			// Check that action exists and that it's not one of the "magic" methods

			if(in_array($this->action, array('_before', '_after')) || $controllerClass->hasMethod($this->action) === false)
			{
				$this->notFound();
			}

			$controllerAction = $controllerClass->getMethod($this->action);
			
			// Check if number of parameters match
			
			if(count($this->actionArgs) < $controllerAction->getNumberOfRequiredParameters() || count($this->actionArgs) > $controllerAction->getNumberOfParameters())
			{
				$this->notFound();
			}
			
			// Check if action is protected or private

			if($controllerAction->isProtected() || $controllerAction->isPrivate())
			{
				$this->forbidden();
			}
			
			// Run pre-action method

			$controller->_before();
			
			// Run action

			$controllerAction->invokeArgs($controller, $this->actionArgs);

			// Run post-action method

			$controller->_after();
		}

		/**
		* Throws a request exception with the 404 status header.
		*
		* @access  public
		*/

		public function notFound()
		{
			if($this->isSubrequest === false)
			{
				Response::instance()->status(404);

				echo static::isCli() ? 'Request failed. The requested controller action does not exist.' . PHP_EOL : View::factory('_errors/404');

				exit();
			}
			else
			{
				throw new BadMethodCallException(__CLASS__ . " Subrequest failed. The requested controller action does not exist.");
			}
		}

		/**
		* Throws a request exception with the 403 status header.
		*
		* @access  public
		*/

		public function forbidden()
		{
			if($this->isSubrequest === false)
			{
				Response::instance()->status(403);
				
				echo static::isCli() ? 'Request failed. The requested controller action is protected or private.' . PHP_EOL : View::factory('_errors/403');

				exit();
			}
			else
			{
				throw new BadMethodCallException(__CLASS__ . " Subrequest failed. The requested controller action is protected or private.");
			}
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
		* Was the request made from the CLI?
		*
		* @access  public
		* @return  boolean
		*/
	
		public static function isCli()
		{
			return static::$isCli;
		}

		/**
		* Is this a subrequest?
		*
		* @access  public
		* @return  boolean
		*/

		public function isSubrequest()
		{
			return $this->isSubrequest;
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
		* @return  boolean
		*/

		public static function referer()
		{
			return static::$referer;
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

		/**
		* Returns the buffered output of the requested controller.
		*
		* @access  public
		* @param   string  URL segments
		* @return  string
		*/

		public static function capture($route)
		{
			ob_start();

			static::factory($route)->execute();

			return ob_get_clean();
		}
	}
}

/** -------------------- End of file --------------------**/