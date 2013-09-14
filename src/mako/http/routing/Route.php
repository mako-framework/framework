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
	 * @var string|Closure
	 */

	protected $action;

	/**
	 * Custom constraints.
	 * 
	 * @var array
	 */

	protected $constraints;

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
	 * @param   string          $route   Route
	 * @param   string|Closure  $action  Route action
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
		return $this->route;
	}

	/**
	 * Returns the route action.
	 * 
	 * @access  public
	 * @return  string|Closure
	 */

	public function getAction()
	{
		return $this->action;
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
	 * Adds a prefix to the route.
	 * 
	 * @access  public
	 * @param   string  $prefix  Route prefix
	 * @return  Route
	 */

	public function prefix($prefix)
	{
		$this->route = '/' . trim($prefix . $this->route, '/');

		return $this;
	}

	/**
	 * Sets the custom constraints.
	 * 
	 * @access  public
	 * @param   array  $constraints  Array of constraints
	 * @return  Route
	 */

	public function constraints(array $constraints)
	{
		$this->constraints = $constraints;

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
		$route = $this->route;

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