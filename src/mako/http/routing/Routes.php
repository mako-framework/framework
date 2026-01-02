<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing;

use Closure;
use mako\common\traits\ExtendableTrait;
use mako\http\routing\exceptions\RoutingException;

use function array_pop;
use function is_string;
use function sprintf;

/**
 * Route collection.
 */
class Routes
{
	use ExtendableTrait;

	/**
	 * Route groups.
	 */
	protected array $groups = [];

	/**
	 * Registered routes.
	 */
	protected array $routes = [];

	/**
	 * Routes grouped by request method.
	 */
	protected array $groupedRoutes = [];

	/**
	 * Named routes.
	 */
	protected array $namedRoutes = [];

	/**
	 * Returns the registered routes.
	 */
	public function getRoutes(): array
	{
		return $this->routes;
	}

	/**
	 * Returns the registered routes that accept the request method.
	 */
	public function getRoutesByMethod(string $method): array
	{
		return $this->groupedRoutes[$method] ?? [];
	}

	/**
	 * Returns TRUE if the named route exists and FALSE if not.
	 */
	public function hasNamedRoute(string $name): bool
	{
		return isset($this->namedRoutes[$name]);
	}

	/**
	 * Returns the named route.
	 */
	public function getNamedRoute(string $name): Route
	{
		if (!isset($this->namedRoutes[$name])) {
			throw new RoutingException(sprintf('No route named [ %s ] has been defined.', $name));
		}

		return $this->namedRoutes[$name];
	}

	/**
	 * Adds a grouped set of routes to the colleciton.
	 */
	public function group(array $options, Closure $routes): void
	{
		$this->groups[] = $options;

		$routes($this);

		array_pop($this->groups);
	}

	/**
	 * Registers a group option.
	 */
	protected function registerGroupOption(Route $route, string $option, mixed $value): void
	{
		if ($option === 'middleware' || $option === 'constraint') {
			foreach ((array) $value as $_key => $_value) {
				if (is_string($_key)) {
					$route->{$option}($_key, ...$_value);
				}
				else {
					$route->{$option}($_value);
				}
			}

			return;
		}

		$route->{$option}($value);
	}

	/**
	 * Registers a route.
	 */
	protected function registerRoute(array $methods, string $route, array|Closure|string $action, ?string $name = null): Route
	{
		$route = new Route($methods, $route, $action, $name);

		$this->routes[] = $route;

		foreach ($methods as $method) {
			$this->groupedRoutes[$method][] = $route;
		}

		if ($name !== null) {
			$this->namedRoutes[$name] = $route;
		}

		if (!empty($this->groups)) {
			foreach ($this->groups as $group) {
				foreach ($group as $option => $value) {
					$this->registerGroupOption($route, $option, $value);
				}
			}
		}

		return $route;
	}

	/**
	 * Adds a route that responds to GET requests to the collection.
	 */
	public function get(string $route, array|Closure|string $action, ?string $name = null): Route
	{
		return $this->registerRoute(['GET', 'HEAD', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to POST requests to the collection.
	 */
	public function post(string $route, array|Closure|string $action, ?string $name = null): Route
	{
		return $this->registerRoute(['POST', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to PUT requests to the collection.
	 */
	public function put(string $route, array|Closure|string $action, ?string $name = null): Route
	{
		return $this->registerRoute(['PUT', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to PATCH requests to the collection.
	 */
	public function patch(string $route, array|Closure|string $action, ?string $name = null): Route
	{
		return $this->registerRoute(['PATCH', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responds to DELETE requests to the collection.
	 */
	public function delete(string $route, array|Closure|string $action, ?string $name = null): Route
	{
		return $this->registerRoute(['DELETE', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that responts to all HTTP methods to the collection.
	 */
	public function all(string $route, array|Closure|string $action, ?string $name = null): Route
	{
		return $this->registerRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'], $route, $action, $name);
	}

	/**
	 * Adds a route that respodns to the chosen HTTP methods to the collection.
	 */
	public function register(array $methods, string $route, array|Closure|string $action, ?string $name = null): Route
	{
		return $this->registerRoute($methods, $route, $action, $name);
	}
}
