<?php

namespace mako\http\routing;

use \Closure;
use \RuntimeException;

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
	 * Filters.
	 * 
	 * @var array
	 */

	protected static $filters = array();

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

	protected static $routes = array();

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
	 * Returns the chosen filter.
	 * 
	 * @access  public
	 * @param   string    $filter  Filter name
	 * @return  \Closure
	 */

	public static function getFilter($filter)
	{
		if(!isset(static::$filters[$filter]))
		{
			throw new RuntimeException(vsprintf("%s(): No filter named [ %s ] has been defined.", array(__METHOD__, $filter)));
		}
		
		return static::$filters[$filter];
	}

	/**
	 * Returns the registered routes.
	 * 
	 * @access  public
	 * @param   string  $method  (optional) HTTP method
	 * @return  array
	 */

	public static function getRoutes()
	{
		return static::$routes;
	}

	/**
	 * Returns TRUE if the named route exists and FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $name  Route name
	 * @return  boolean
	 */

	public static function hasNamedRoute($name)
	{
		return isset(static::$namedRoutes[$name]);
	}

	/**
	 * Returns the named route.
	 * 
	 * @access  public
	 * @param   string  $name  Route name
	 * @return  string
	 */

	public static function getNamedRoute($name)
	{
		if(!isset(static::$namedRoutes[$name]))
		{
			throw new RuntimeException(vsprintf("%s(): No route named [ %s ] has been defined.", array(__METHOD__, $name)));
		}

		return static::$namedRoutes[$name];
	}

	/**
	 * Adds a filter.
	 * 
	 * @access  public
	 * @param   string    $name    Filter name
	 * @param   \Closure  $filter  Filter
	 */

	public static function filter($name, Closure $filter)
	{
		static::$filters[$name] = $filter;
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
		$route = new Route($methods, $route, $action, $name);

		static::$routes[] = $route;

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
		return static::addRoute(array('GET', 'HEAD', 'OPTIONS'), $route, $action, $name);
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
		return static::addRoute(array('POST', 'OPTIONS'), $route, $action, $name);
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
		return static::addRoute(array('PUT', 'OPTIONS'), $route, $action, $name);
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
		return static::addRoute(array('PATCH', 'OPTIONS'), $route, $action, $name);
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
		return static::addRoute(array('DELETE', 'OPTIONS'), $route, $action, $name);
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
		return static::addRoute(array('GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'), $route, $action, $name);
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