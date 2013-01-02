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
	 * @var mako\Request
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
	 * @param   mako\Request  $request  Request that instantiated the router
	 * @param   string        $route    Request route
	 * @param   string        $package  (optional) Package name
	 */

	public function __construct(Request $request, $route, $package = null)
	{
		$this->request = $request;
		$this->route   = $route;
		$this->package = $package;
		$this->config  = Config::get(($package !== null ? $package . '::' : '') . 'routes');

		if($this->request->isMain() && $package === null)
		{
			static::$packageBaseRoutes = array_flip($this->config['packages']);
		}

	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

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

		if($this->package === null)
		{
			if(!empty($this->config['packages']))
			{
				foreach($this->config['packages'] as $base => $package)
				{
					if($route === $base || strpos($route, $base . '/') === 0)
					{
						ClassLoader::registerNamespace($package . '\controllers', MAKO_PACKAGES_PATH . '/' . $package . '/controllers');
						
						Package::init($package);

						$router = new static($this->request, trim(mb_substr($route, mb_strlen($base)), '/'), $package);

						return $router->route();
					}
				}
			}
		}

		// Return false if none of the custom routes matched when automapping is disabled

		if($this->request->isMain() && !Config::get('routes.automap') && !$matched && $route !== $this->config['default_route'])
		{
			return false;
		}

		// Route the request

		$namespace        = $this->package === null ? '\app\controllers\\' : '\\' . $this->package . '\controllers\\';
		$controller       = '';
		$action           = '';
		$actionArguments  = array();
		$controllerPath   = $this->package === null ? MAKO_APPLICATION_PATH . '/controllers/' : MAKO_PACKAGES_PATH . '/' . $this->package . '/controllers/';

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

				$controllerPath .= $segment . '/';
				$namespace      .= $segment . '\\';

				array_shift($segments);

				continue;
			}
			elseif(is_file($path . '.php'))
			{
				// We have found our controller - Exit loop

				$controller = $segment;

				array_shift($segments);

				break;
			}
			else
			{
				// No directory or controller - Stop routing

				return false;
			}
		}

		if(empty($controller))
		{
			$controller = 'index'; // default controller
		}

		// Get the action we want to execute

		$action = array_shift($segments);

		if($action === null)
		{
			$action = 'index';
		}

		// Remaining segments are passed as arguments to the action

		$actionArguments = $segments;

		// Check if file exists

		if(!file_exists($controllerPath . $controller . '.php'))
		{
			return false;
		}
		else
		{
			$this->request->setNamespace($namespace);
			$this->request->setController($controller);
			$this->request->setAction($action);
			$this->request->setActionArguments($actionArguments);

			return true;
		}
	}
}

/** -------------------- End of file --------------------**/