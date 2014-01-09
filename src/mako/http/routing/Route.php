<?php

namespace mako\http\routing;

/**
 * Route.
 * 
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Route
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Methods.
	 * 
	 * @var array
	 */

	protected $methods;

	/**
	 * Route.
	 * 
	 * @var string
	 */

	protected $route;

	/**
	 * Route action.
	 * 
	 * @var string|\Closure
	 */

	protected $action;

	/**
	 * Route name.
	 * 
	 * @var string
	 */

	protected $name;

	/**
	 * Route prefix.
	 * 
	 * @var string
	 */

	protected $prefix;

	/**
	 * Does the route have a trailing slash?
	 * 
	 * @var boolean
	 */

	protected $hasTrailingSlash;

	/**
	 * Route constraints.
	 * 
	 * @var array
	 */

	protected $constraints = [];

	/**
	 * Before filters.
	 * 
	 * @var array
	 */

	protected $beforeFilters = [];

	/**
	 * After filters.
	 * 
	 * @var array
	 */

	protected $afterFilters = [];

	/**
	 * Matched parameters.
	 * 
	 * @var array
	 */

	protected $parameters = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   array            $methods  Route methods
	 * @param   string           $route    Route
	 * @param   string|\Closure  $action   Route action
	 * @param   string           $name     Route name
	 */

	public function __construct(array $methods, $route, $action, $name = null)
	{
		$this->methods = $methods;

		$this->route = $route;
		
		$this->action = $action;

		$this->name = $name;

		$this->hasTrailingSlash = (substr($route, -1) === '/');
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the HTTP methods the route responds to.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function getMethods()
	{
		return $this->methods;
	}

	/**
	 * Returns the route.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getRoute()
	{
		return $this->prefix . $this->route;
	}

	/**
	 * Returns the route action.
	 * 
	 * @access  public
	 * @return  string|\Closure
	 */

	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Returns the route name.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the before filters.
	 * 
	 * @access  public
	 * @return  aray
	 */

	public function getBeforeFilters()
	{
		return $this->beforeFilters;
	}

	/**
	 * Returns the after filters.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function getAfterFilters()
	{
		return $this->afterFilters;
	}

	/**
	 * Returns the matched route parameters.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * Returns a route parameter.
	 * 
	 * @access  public
	 * @param   string  $key      Parameter name
	 * @param   mixed   $default  (optional) Default value
	 * @return  mixed
	 */

	public function param($key, $default = null)
	{
		return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
	}

	/**
	 * Adds a set of before filters.
	 * 
	 * @access  public
	 * @param   array|string|\Closure     $filters  Filters
	 * @return  \mako\http\routing\Route
	 */

	public function before($filters)
	{
		$this->beforeFilters = array_merge($this->beforeFilters, (array) $filters);

		return $this;
	}

	/**
	 * Adds a set of after filters.
	 * 
	 * @access  public
	 * @param   array|string|\Closure     $filters  Filters
	 * @return  \mako\http\routing\Route
	 */

	public function after($filters)
	{
		$this->afterFilters = array_merge($this->afterFilters, (array) $filters);

		return $this;
	}

	/**
	 * Adds a prefix to the route.
	 * 
	 * @access  public
	 * @param   string                    $prefix  Route prefix
	 * @return  \mako\http\routing\Route
	 */

	public function prefix($prefix)
	{
		if(!empty($prefix))
		{
			$this->prefix .= '/' . trim($prefix, '/');
		}
		
		return $this;
	}

	/**
	 * Sets the custom constraints.
	 * 
	 * @access  public
	 * @param   array  $constraints       Array of constraints
	 * @return  \mako\http\routing\Route
	 */

	public function constraints(array $constraints)
	{
		$this->constraints = $constraints + $this->constraints;

		return $this;
	}

	/**
	 * Returns TRUE if the route allows the specified method or FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $method  Method
	 * @return  boolean
	 */

	public function allows($method)
	{
		return in_array($method, $this->methods);
	}

	/**
	 * Returns TRUE if the route has a trailing slash and FALSE if not.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function hasTrailingSlash()
	{
		return $this->hasTrailingSlash;
	}

	/**
	 * Returns the regex needed to match the route.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function getRouteRegex()
	{
		$route = $this->getRoute();

		if(strpos($route, '?'))
		{
			$route = preg_replace('/\/{(\w+)}\?/', '(?:/{$1})?', $route);
		}

		if(!empty($this->constraints))
		{
			foreach($this->constraints as $key => $constraint)
			{
				$route = str_replace('{' . $key . '}', '(?P<' . $key . '>' . $constraint . ')', $route);
			}
		}

		$route = preg_replace('/{(!?[^0-9][\w]+)}/', '(?P<$1>[^/]++)', $route);

		if($this->hasTrailingSlash)
		{
			$route .= '?';
		}

		return '#^' . $route . '$#s';
	}

	/**
	 * Collects the named parameters.
	 * 
	 * @access  protected
	 * @param   array      $matches  Regex matches
	 */

	protected function collectNamedParameters(array $matches)
	{
		foreach($matches as $key => $value)
		{
			if(is_int($key))
			{
				unset($matches[$key]);
			}
		}

		$this->parameters = $matches;
	}

	/**
	 * Checks if the route patern matches the provided route.
	 * 
	 * @access  public
	 * @param   string   $route  Route to match
	 * @return  boolean
	 */

	public function isMatch($route)
	{
		if(preg_match($this->getRouteRegex(), $route, $matches) > 0)
		{
			$this->collectNamedParameters($matches);

			return true;
		}

		return false;
	}
}

/** -------------------- End of file -------------------- **/