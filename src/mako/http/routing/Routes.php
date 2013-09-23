<?php

namespace mako\http\routing;

use \Closure;

/**
 * Route collection.
 * 
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Routes
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Route groups.
	 * 
	 * @var array
	 */

	protected static $groups = array();

	/**
	 * Registered routes.
	 * 
	 * @var array
	 */

	protected static $routes = array
	(
		'HEAD'   => array(),
		'GET'    => array(),
		'POST'   => array(),
		'PUT'    => array(),
		'PATCH'  => array(),
		'DELETE' => array(),
	);

	/**
	 * Named routes.
	 * 
	 * @var array
	 */

	protected static $namedRoutes = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Protected constructor since this is a static class.
	 *
	 * @access  protected
	 */

	protected function __construct()
	{
		// Nothing here
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the registered routes.
	 * 
	 * @access  public
	 * @param   string  $method  (optional) HTTP method
	 * @return  array
	 */

	public static function getRoutes($method = null)
	{
		return is_null($method) ? static::$routes : static::$routes[$method];
	}

	/**
	 * Returns the named route.
	 * 
	 * @access  public
	 * @param   string          $name  Route name
	 * @return  string|boolean
	 */

	public static function getNamedRoute($name)
	{
		return isset(static::$namedRoutes[$name]) ? static::$namedRoutes[$name] : false;
	}

	/**
	 * Adds a grouped set of routes to the colleciton.
	 * 
	 * @access  public
	 * @param   array     $options  Group options
	 * @param   \Closure  $routes   Route closure
	 */

	public static function group(array $options, Closure $routes)
	{
		static::$groups[] = $options;

		$routes();

		array_pop(static::$groups);
	}

	/**
	 * Adds a route to the collection.
	 * 
	 * @access  public
	 * @param   array            $methods  HTTP methods
	 * @param   string           $route    Route
	 * @param   string|\Closure  $action   Route action
	 * @param   string           $name     (optional) Route name
	 */

	protected static function addRoute(array $methods, $route, $action, $name = null)
	{
		$route = new Route($route, $action);

		foreach($methods as $method)
		{
			static::$routes[$method][] = $route;
		}

		if(!is_null($name))
		{
			static::$namedRoutes[$name] = $route;
		}

		if(!empty(static::$groups))
		{
			foreach(static::$groups as $group)
			{
				foreach($group as $option => $value)
				{
					$route->$option($value);
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
	 * @param   string           $name    (optional) Route name
	 */

	public static function get($route, $action, $name = null)
	{
		return static::addRoute(array('HEAD', 'GET'), $route, $action, $name);
	}

	/**
	 * Adds a route that responds to POST requests to the collection.
	 * 
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    (optional) Route name
	 */

	public static function post($route, $action, $name = null)
	{
		return static::addRoute(array('POST'), $route, $action, $name);
	}

	/**
	 * Adds a route that responds to PUT requests to the collection.
	 * 
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    (optional) Route name
	 */

	public static function put($route, $action, $name = null)
	{
		return static::addRoute(array('PUT'), $route, $action, $name);
	}

	/**
	 * Adds a route that responds to PATCH requests to the collection.
	 * 
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    (optional) Route name
	 */

	public static function patch($route, $action, $name = null)
	{
		return static::addRoute(array('PATCH'), $route, $action, $name);
	}

	/**
	 * Adds a route that responds to DELETE requests to the collection.
	 * 
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    (optional) Route name
	 */

	public static function delete($route, $action, $name = null)
	{
		return static::addRoute(array('DELETE'), $route, $action, $name);
	}

	/**
	 * Adds a route that responts to all HTTP methods to the collection.
	 * 
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    (optional) Route name
	 */

	public static function all($route, $action, $name = null)
	{
		return static::addRoute(array('HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE'), $route, $action, $name);
	}

	/**
	 * Adds a route that respodns to the chosen HTTP methods to the collection.
	 * 
	 * @access  public
	 * @param   array            $methods  Array of HTTP methods the route should respond to
	 * @param   string           $route    Route
	 * @param   string|\Closure  $action   Route action
	 * @param   string           $name     (optional) Route name
	 */

	public static function methods(array $methods, $route, $action, $name = null)
	{
		return static::addRoute($methods, $route, $action, $name);
	}
}

/** -------------------- End of file -------------------- **/