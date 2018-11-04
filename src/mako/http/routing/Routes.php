<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing;

use Closure;
use mako\common\traits\ExtendableTrait;
use RuntimeException;

use function array_pop;
use function vsprintf;

/**
 * Route collection.
 *
 * @author Frederic G. Østby
 */
class Routes
{
	use ExtendableTrait;

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
	 * Routes grouped by request method.
	 *
	 * @var array
	 */
	protected $groupedRoutes = [];

	/**
	 * Named routes.
	 *
	 * @var array
	 */
	protected $namedRoutes = [];

	/**
	 * Returns the registered routes.
	 *
	 * @return array
	 */
	public function getRoutes(): array
	{
		return $this->routes;
	}

	/**
	 * Returns the registered routes that accept the request method.
	 *
	 * @param  string $method Request method
	 * @return array
	 */
	public function getRoutesByMethod(string $method): array
	{
		return $this->groupedRoutes[$method] ?? [];
	}

	/**
	 * Returns TRUE if the named route exists and FALSE if not.
	 *
	 * @param  string $name Route name
	 * @return bool
	 */
	public function hasNamedRoute(string $name): bool
	{
		return isset($this->namedRoutes[$name]);
	}

	/**
	 * Returns the named route.
	 *
	 * @param  string                   $name Route name
	 * @return \mako\http\routing\Route
	 */
	public function getNamedRoute(string $name): Route
	{
		if(!isset($this->namedRoutes[$name]))
		{
			throw new RuntimeException(vsprintf('No route named [ %s ] has been defined.', [$name]));
		}

		return $this->namedRoutes[$name];
	}

	/**
	 * Adds a grouped set of routes to the colleciton.
	 *
	 * @param array    $options Group options
	 * @param \Closure $routes  Route closure
	 */
	public function group(array $options, Closure $routes)
	{
		$this->groups[] = $options;

		$routes($this);

		array_pop($this->groups);
	}

	/**
	 * Registers a route.
	 *
	 * @param  array                    $methods HTTP methods
	 * @param  string                   $route   Route
	 * @param  string|\Closure          $action  Route action
	 * @param  string|null              $name    Route name
	 * @return \mako\http\routing\Route
	 */
	protected function registerRoute(array $methods, string $route, $action, string $name = null): Route
	{
		$route = new Route($methods, $route, $action, $name);

		$this->routes[] = $route;

		foreach($methods as $method)
		{
			$this->groupedRoutes[$method][] = $route;
		}

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
					$route->{$option}($value);
				}
			}
		}

		return $route;
	}

	/**
	 * Adds a route that responds to GET requests to the collection.
	 *
	 * @param  string                   $route  Route
	 * @param  string|\Closure          $action Route action
	 * @param  string|null              $name   Route name
	 * @return \mako\http\routing\Route
	 */
	public function get(string $route, $action, string $name = null): Route
	{
		return $this->registerRoute(['GET', 'HEAD', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to POST requests to the collection.
	 *
	 * @param  string                   $route  Route
	 * @param  string|\Closure          $action Route action
	 * @param  string|null              $name   Route name
	 * @return \mako\http\routing\Route
	 */
	public function post(string $route, $action, string $name = null): Route
	{
		return $this->registerRoute(['POST', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to PUT requests to the collection.
	 *
	 * @param  string                   $route  Route
	 * @param  string|\Closure          $action Route action
	 * @param  string|null              $name   Route name
	 * @return \mako\http\routing\Route
	 */
	public function put(string $route, $action, string $name = null): Route
	{
		return $this->registerRoute(['PUT', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to PATCH requests to the collection.
	 *
	 * @param  string                   $route  Route
	 * @param  string|\Closure          $action Route action
	 * @param  string|null              $name   Route name
	 * @return \mako\http\routing\Route
	 */
	public function patch(string $route, $action, string $name = null): Route
	{
		return $this->registerRoute(['PATCH', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to DELETE requests to the collection.
	 *
	 * @param  string                   $route  Route
	 * @param  string|\Closure          $action Route action
	 * @param  string|null              $name   Route name
	 * @return \mako\http\routing\Route
	 */
	public function delete(string $route, $action, string $name = null): Route
	{
		return $this->registerRoute(['DELETE', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responts to all HTTP methods to the collection.
	 *
	 * @param  string                   $route  Route
	 * @param  string|\Closure          $action Route action
	 * @param  string|null              $name   Route name
	 * @return \mako\http\routing\Route
	 */
	public function all(string $route, $action, string $name = null): Route
	{
		return $this->registerRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that respodns to the chosen HTTP methods to the collection.
	 *
	 * @param  array                    $methods Array of HTTP methods the route should respond to
	 * @param  string                   $route   Route
	 * @param  string|\Closure          $action  Route action
	 * @param  string|null              $name    Route name
	 * @return \mako\http\routing\Route
	 */
	public function register(array $methods, string $route, $action, string $name = null): Route
	{
		return $this->registerRoute($methods, $route, $action, $name);
	}
}
