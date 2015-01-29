<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use Closure;
use RuntimeException;

use mako\http\routing\Route;

/**
 * Route collection.
 *
 * @author  Frederic G. Østby
 */

class Routes
{
	/**
	 * Route groups.
	 *
	 * @var array
	 */

	protected $groups = [];

	/**
	 * Registered routes.
	 *
	 * @var array
	 */

	protected  $routes = [];

	/**
	 * Named routes.
	 *
	 * @var array
	 */

	protected $namedRoutes = [];

	/**
	 * Returns the registered routes.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Returns TRUE if the named route exists and FALSE if not.
	 *
	 * @access  public
	 * @param   string   $name  Route name
	 * @return  boolean
	 */

	public function hasNamedRoute($name)
	{
		return isset($this->namedRoutes[$name]);
	}

	/**
	 * Returns the named route.
	 *
	 * @access  public
	 * @param   string                   $name  Route name
	 * @return  \mako\http\routes\Route
	 */

	public function getNamedRoute($name)
	{
		if(!isset($this->namedRoutes[$name]))
		{
			throw new RuntimeException(vsprintf("%s(): No route named [ %s ] has been defined.", [__METHOD__, $name]));
		}

		return $this->namedRoutes[$name];
	}

	/**
	 * Adds a grouped set of routes to the colleciton.
	 *
	 * @access  public
	 * @param   array     $options  Group options
	 * @param   \Closure  $routes   Route closure
	 */

	public function group(array $options, Closure $routes)
	{
		$this->groups[] = $options;

		$routes($this);

		array_pop($this->groups);
	}

	/**
	 * Returns the real route method name.
	 *
	 * @access  public
	 * @param   string  $method  Method name
	 * @return  string
	 */

	protected function getRealMethodName($method)
	{
		return str_replace(['namespace'], ['setNamespace'], $method);
	}

	/**
	 * Registers a route.
	 *
	 * @access  public
	 * @param   array            $methods  HTTP methods
	 * @param   string           $route    Route
	 * @param   string|\Closure  $action   Route action
	 * @param   string           $name     Route name
	 */

	protected function registerRoute(array $methods, $route, $action, $name = null)
	{
		$route = new Route($methods, $route, $action, $name);

		$this->routes[] = $route;

		if($name !== null)
		{
			$this->namedRoutes[$name] = $route;
		}

		if(!empty($this->groups))
		{
			foreach($this->groups as $group)
			{
				foreach($group as $option => $value)
				{
					$route->{$this->getRealMethodName($option)}($value);
				}
			}
		}

		return $route;
	}

	/**
	 * Adds a route that responds to GET requests to the collection.
	 *
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    Route name
	 */

	public function get($route, $action, $name = null)
	{
		return $this->registerRoute(['GET', 'HEAD', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to POST requests to the collection.
	 *
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    Route name
	 */

	public function post($route, $action, $name = null)
	{
		return $this->registerRoute(['POST', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to PUT requests to the collection.
	 *
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    Route name
	 */

	public function put($route, $action, $name = null)
	{
		return $this->registerRoute(['PUT', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to PATCH requests to the collection.
	 *
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    Route name
	 */

	public function patch($route, $action, $name = null)
	{
		return $this->registerRoute(['PATCH', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to DELETE requests to the collection.
	 *
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    Route name
	 */

	public function delete($route, $action, $name = null)
	{
		return $this->registerRoute(['DELETE', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responts to all HTTP methods to the collection.
	 *
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    Route name
	 */

	public function all($route, $action, $name = null)
	{
		return $this->registerRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that respodns to the chosen HTTP methods to the collection.
	 *
	 * @access  public
	 * @param   array            $methods  Array of HTTP methods the route should respond to
	 * @param   string           $route    Route
	 * @param   string|\Closure  $action   Route action
	 * @param   string           $name     Route name
	 */

	public function methods(array $methods, $route, $action, $name = null)
	{
		return $this->registerRoute($methods, $route, $action, $name);
	}
}