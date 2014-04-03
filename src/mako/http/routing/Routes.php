<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use \Closure;
use \RuntimeException;

/**
 * Route collection.
 * 
 * @author  Frederic G. Østby
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

	protected $filters = [];

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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 */

	public function __construct()
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

	public function getFilter($filter)
	{
		if(!isset($this->filters[$filter]))
		{
			throw new RuntimeException(vsprintf("%s(): No filter named [ %s ] has been defined.", [__METHOD__, $filter]));
		}
		
		return $this->filters[$filter];
	}

	/**
	 * Returns the registered routes.
	 * 
	 * @access  public
	 * @param   string  $method  (optional) HTTP method
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
	 * @param   string  $name  Route name
	 * @return  string
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
	 * Adds a filter.
	 * 
	 * @access  public
	 * @param   string    $name    Filter name
	 * @param   \Closure  $filter  Filter
	 */

	public function filter($name, Closure $filter)
	{
		$this->filters[$name] = $filter;
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

		$routes();

		array_pop($this->groups);
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

	protected function addRoute(array $methods, $route, $action, $name = null)
	{
		$route = new Route($methods, $route, $action, $name);

		$this->routes[] = $route;

		if(!is_null($name))
		{
			$this->namedRoutes[$name] = $route;
		}

		if(!empty($this->groups))
		{
			foreach($this->groups as $group)
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

	public function get($route, $action, $name = null)
	{
		return $this->addRoute(['GET', 'HEAD', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to POST requests to the collection.
	 * 
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    (optional) Route name
	 */

	public function post($route, $action, $name = null)
	{
		return $this->addRoute(['POST', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to PUT requests to the collection.
	 * 
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    (optional) Route name
	 */

	public function put($route, $action, $name = null)
	{
		return $this->addRoute(['PUT', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to PATCH requests to the collection.
	 * 
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    (optional) Route name
	 */

	public function patch($route, $action, $name = null)
	{
		return $this->addRoute(['PATCH', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to DELETE requests to the collection.
	 * 
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    (optional) Route name
	 */

	public function delete($route, $action, $name = null)
	{
		return $this->addRoute(['DELETE', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responts to all HTTP methods to the collection.
	 * 
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 * @param   string           $name    (optional) Route name
	 */

	public function all($route, $action, $name = null)
	{
		return $this->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'], $route, $action, $name);
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

	public function methods(array $methods, $route, $action, $name = null)
	{
		return $this->addRoute($methods, $route, $action, $name);
	}
}