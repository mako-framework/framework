<?php

namespace mako\request;

use \mako\Config;
use \mako\Request;
use \mako\Package;
use \mako\ClassLoader;

/**
 * Request router.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Router
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Request that instantiated the router.
	 * 
	 * @var \mako\Request
	 */

	protected $request;

	/**
	 * Request route.
	 * 
	 * @var string
	 */

	protected $route;

	/**
	 * Package name
	 * 
	 * @var string
	 */

	protected $package;

	/**
	 * Route configuration.
	 * 
	 * @var array
	 */

	protected $config;

	/**
	 * Controller namespace.
	 * 
	 * @var string
	 */

	protected $namespace;

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
	 * Controller arguments.
	 * 
	 * @var array
	 */

	protected $actionArguments = array();

	/**
	 * Base routes of the routable packages.
	 *
	 * @var arary
	 */

	protected static $packageBaseRoutes;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\Request  $request  Request that instantiated the router
	 */

	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->route   = $request->route();
		$this->config  = Config::get('routes');

		if($this->request->isMain() && $this->package === null)
		{
			static::$packageBaseRoutes = array_flip($this->config['packages']);
		}

	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the controller namespace.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * Returns the controller name.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Returns the controller action.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Returns the action arguments.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function getActionArguments()
	{
		return $this->actionArguments;
	}

	/**
	 * Replaces the package prefix with the package base route.
	 * 
	 * @access  public
	 * @param   string  $route  Route
	 * @return  string
	 */

	public static function packageRoute($route)
	{
		$packageBaseRoutes = static::$packageBaseRoutes;

		return preg_replace_callback('/^([a-z_0-9]+::)/', function($matches) use ($packageBaseRoutes)
		{
			$package = substr($matches[1], 0, -2);

			return isset($packageBaseRoutes[$package]) ? $packageBaseRoutes[$package] . '/' : $matches[1];
		}, $route, 1);
	}

	/**
	 * Routes the request. Returns TRUE if a controller is found and FALSE if not.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function route()
	{
		if($this->route === '')
		{
			$route = $this->config['default_route'];
		}
		else
		{
			$route = trim($this->route, '/');
		}

		// Replace the package prefix with the package base route

		if(!$this->request->isMain() && strpos($route, '::') !== false)
		{
			$route = static::packageRoute($route);
		}

		// Re-route custom routes

		$matched = false;

		if(!empty($this->config['custom_routes']))
		{
			foreach($this->config['custom_routes'] as $pattern => $realRoute)
			{
				if(preg_match('#^' . $pattern . '$#iu', $route) === 1)
				{
					if(strpos($realRoute, '$') !== false)
					{
						$realRoute = preg_replace('#^' . $pattern . '$#iu', $realRoute, $route);
					}

					$route = trim($realRoute, '/');

					$matched = true;

					break;
				}
			}
		}

		// Is the route pointing to a package?

		if($this->package === null && !empty($this->config['packages']))
		{
			foreach($this->config['packages'] as $base => $package)
			{
				if($route === $base || strpos($route, $base . '/') === 0)
				{	
					Package::init($package);

					$this->route   = trim(mb_substr($route, mb_strlen($base)), '/');
					$this->package = $package;
					$this->config  = Config::get($package . '::routes');

					return $this->route();
				}
			}
		}

		// Return false if none of the custom routes matched when automapping is disabled

		if($this->request->isMain() && !Config::get('routes.automap') && !$matched && $route !== $this->config['default_route'])
		{
			return false;
		}

		// Route the request

		$this->namespace = $this->package === null ? '\app\controllers\\' : '\\' . $this->package . '\controllers\\';
		$controllerPath  = $this->package === null ? MAKO_APPLICATION_PATH . '/controllers/' : MAKO_PACKAGES_PATH . '/' . $this->package . '/controllers/';

		// Get the URL segments

		$segments = explode('/', $route, 100);

		foreach($segments as $segment)
		{
			if($segment === '.' || $segment === '..')
			{
				return false;
			}

			$path = $controllerPath . $segment;

			if(is_dir($path))
			{
				// Just a directory - Jump to next iteration

				$controllerPath  .= $segment . '/';
				$this->namespace .= $segment . '\\';

				array_shift($segments);

				continue;
			}
			elseif(is_file($path . '.php'))
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

		// Remaining segments are passed as arguments to the action

		$this->actionArguments = $segments;

		// Check if file exists

		if(!file_exists($controllerPath . $this->controller . '.php'))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}

/** -------------------- End of file -------------------- **/