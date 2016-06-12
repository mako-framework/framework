<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use Closure;

/**
 * Route.
 *
 * @author  Frederic G. Østby
 */
class Route
{
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
	 * Route action namespace.
	 *
	 * @var string
	 */
	protected $namespace;

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
	 * Middleware.
	 *
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   array            $methods  Route methods
	 * @param   string           $route    Route
	 * @param   string|\Closure  $action   Route action
	 * @param   string           $name     Route name
	 */
	public function __construct(array $methods, string $route, $action, string $name = null)
	{
		$this->methods = $methods;

		$this->route = $route;

		$this->action = $action;

		$this->name = $name;

		$this->hasTrailingSlash = (substr($route, -1) === '/');
	}

	/**
	 * Returns the HTTP methods the route responds to.
	 *
	 * @access  public
	 * @return  array
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

	/**
	 * Returns the route.
	 *
	 * @access  public
	 * @return  string
	 */
	public function getRoute(): string
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
		if($this->action instanceof Closure || empty($this->namespace))
		{
			return $this->action;
		}

		return $this->namespace . $this->action;
	}

	/**
	 * Returns the route name.
	 *
	 * @access  public
	 * @return  null|string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the middleware.
	 *
	 * @access  public
	 * @return  array
	 */
	public function getMiddleware(): array
	{
		return $this->middleware;
	}

	/**
	 * Sets the route action namespace.
	 *
	 * @access  public
	 * @param   string                    $namespace  Route action namespace
	 * @return  \mako\http\routing\Route
	 */
	public function namespace(string $namespace): Route
	{
		$this->namespace .= $namespace . '\\';

		return $this;
	}

	/**
	 * Adds a prefix to the route.
	 *
	 * @access  public
	 * @param   string                    $prefix  Route prefix
	 * @return  \mako\http\routing\Route
	 */
	public function prefix(string $prefix): Route
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
	 * @param   array                     $constraints  Array of constraints
	 * @return  \mako\http\routing\Route
	 */
	public function when(array $constraints): Route
	{
		$this->constraints = $constraints + $this->constraints;

		return $this;
	}

	/**
	 * Adds a set of middleware.
	 *
	 * @access  public
	 * @param   array|string              $middleware  Middleware
	 * @return  \mako\http\routing\Route
	 */
	public function middleware($middleware): Route
	{
		$this->middleware = array_merge($this->middleware, (array) $middleware);

		return $this;
	}

	/**
	 * Returns TRUE if the route allows the specified method or FALSE if not.
	 *
	 * @access  public
	 * @param   string   $method  Method
	 * @return  boolean
	 */
	public function allows(string $method): bool
	{
		return in_array($method, $this->methods);
	}

	/**
	 * Returns TRUE if the route has a trailing slash and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function hasTrailingSlash(): bool
	{
		return $this->hasTrailingSlash;
	}

	/**
	 * Returns the regex needed to match the route.
	 *
	 * @access  public
	 * @return  string
	 */
	public function getRegex(): string
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

		$route = preg_replace('/{((\d*[a-z_]\d*)+)}/i', '(?P<$1>[^/]++)', $route);

		if($this->hasTrailingSlash)
		{
			$route .= '?';
		}

		return '#^' . $route . '$#su';
	}
}