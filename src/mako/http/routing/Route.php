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
	 * Route prefix.
	 * 
	 * @var string
	 */

	protected $prefix;

	/**
	 * Route constraints.
	 * 
	 * @var array
	 */

	protected $constraints = array();

	/**
	 * Before filters.
	 * 
	 * @var array
	 */

	protected $beforeFilters = array();

	/**
	 * After filters.
	 * 
	 * @var array
	 */

	protected $afterFilters = array();

	/**
	 * Matched parameters.
	 * 
	 * @var array
	 */

	protected $parameters = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string           $route   Route
	 * @param   string|\Closure  $action  Route action
	 */

	public function __construct($route, $action)
	{
		$this->route = $route;
		
		$this->action = $action;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

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
	 * Returns the matched parameters.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function getParameters()
	{
		return $this->parameters;
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

		return '#^' . preg_replace('/{(\w+)}/', '(?P<$1>[^/]++)', $route) . '$#s';
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