<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing;

use Closure;
use mako\http\routing\attributes\Constraint;
use mako\http\routing\attributes\Middleware;
use ReflectionClass;
use ReflectionMethod;

use function in_array;
use function is_array;
use function preg_replace;
use function str_replace;
use function strpos;
use function substr;
use function trim;

/**
 * Route.
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
	 * @var array|\Closure|string
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
	 * @var bool
	 */
	protected $hasTrailingSlash;

	/**
	 * Route patterns.
	 *
	 * @var array
	 */
	protected $patterns = [];

	/**
	 * Middleware.
	 *
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * Constraints.
	 *
	 * @var array
	 */
	protected $constraints = [];

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * Constructor.
	 *
	 * @param array                 $methods Route methods
	 * @param string                $route   Route
	 * @param array|\Closure|string $action  Route action
	 * @param string|null           $name    Route name
	 */
	public function __construct(array $methods, string $route, $action, ?string $name = null)
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
	 * @return array
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

	/**
	 * Returns the route.
	 *
	 * @return string
	 */
	public function getRoute(): string
	{
		return "{$this->prefix}{$this->route}";
	}

	/**
	 * Returns the route action.
	 *
	 * @return array|\Closure|string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Returns the route name.
	 *
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Returns action middleware.
	 *
	 * @return array
	 */
	protected function getActionMiddleware(): array
	{
		$middleware = [];

		[$class, $method] = is_array($this->action) ? $this->action : [$this->action, '__invoke'];

		foreach((new ReflectionClass($class))->getAttributes(Middleware::class) as $attribute)
		{
			/** @var \mako\http\routing\attributes\Middleware $middlewareAttribute */
			$middlewareAttribute = $attribute->newInstance();

			$middleware = [...$middleware, ...$middlewareAttribute->getMiddleware()];
		}

		foreach((new ReflectionMethod($class, $method))->getAttributes(Middleware::class) as $attribute)
		{
			/** @var \mako\http\routing\attributes\Middleware $middlewareAttribute */
			$middlewareAttribute = $attribute->newInstance();

			$middleware = [...$middleware, ...$middlewareAttribute->getMiddleware()];
		}

		return $middleware;
	}

	/**
	 * Returns the middleware.
	 *
	 * @return array
	 */
	public function getMiddleware(): array
	{
		if($this->action instanceof Closure || PHP_VERSION_ID < 80000)
		{
			return $this->middleware;
		}

		return [...$this->middleware, ...$this->getActionMiddleware()];
	}

	/**
	 * Returns action constraints.
	 *
	 * @return array
	 */
	protected function getActionConstraints(): array
	{
		$constraints = [];

		[$class, $method] = is_array($this->action) ? $this->action : [$this->action, '__invoke'];

		foreach((new ReflectionClass($class))->getAttributes(Constraint::class) as $attribute)
		{
			/** @var \mako\http\routing\attributes\Constraint $constraintAttribute */
			$constraintAttribute = $attribute->newInstance();

			$constraints = [...$constraints, ...$constraintAttribute->getConstraints()];
		}

		foreach((new ReflectionMethod($class, $method))->getAttributes(Constraint::class) as $attribute)
		{
			/** @var \mako\http\routing\attributes\Constraint $constraintAttribute */
			$constraintAttribute = $attribute->newInstance();

			$constraints = [...$constraints, ...$constraintAttribute->getConstraints()];
		}

		return $constraints;
	}

	/**
	 * Returns the constraints.
	 *
	 * @return array
	 */
	public function getConstraints(): array
	{
		if($this->action instanceof Closure || PHP_VERSION_ID < 80000)
		{
			return $this->constraints;
		}

		return [...$this->constraints, ...$this->getActionConstraints()];
	}

	/**
	 * Sets the route parameters.
	 *
	 * @param array $parameters Parameters
	 */
	public function setParameters(array $parameters): void
	{
		$this->parameters = $parameters;
	}

	/**
	 * Returns all the route parameters.
	 *
	 * @return array
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * Returns the named parameter value.
	 *
	 * @param  string $name    Parameter name
	 * @param  mixed  $default Default value
	 * @return mixed
	 */
	public function getParameter(string $name, $default = null)
	{
		return $this->parameters[$name] ?? $default;
	}

	/**
	 * Adds a prefix to the route.
	 *
	 * @param  string                   $prefix Route prefix
	 * @return \mako\http\routing\Route
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
	 * Sets the custom patterns.
	 *
	 * @param  array                    $patterns Array of patterns
	 * @return \mako\http\routing\Route
	 */
	public function patterns(array $patterns): Route
	{
		$this->patterns = $patterns + $this->patterns;

		return $this;
	}

	/**
	 * Adds a set of middleware.
	 *
	 * @param  array|string             $middleware Middleware
	 * @return \mako\http\routing\Route
	 */
	public function middleware($middleware): Route
	{
		$this->middleware = [...$this->middleware, ...(array) $middleware];

		return $this;
	}

	/**
	 * Adds a set of constraints.
	 *
	 * @param  array|string             $constraint Constraint
	 * @return \mako\http\routing\Route
	 */
	public function constraint($constraint): Route
	{
		$this->constraints = [...$this->constraints, ...(array) $constraint];

		return $this;
	}

	/**
	 * Returns TRUE if the route allows the specified method or FALSE if not.
	 *
	 * @param  string $method Method
	 * @return bool
	 */
	public function allowsMethod(string $method): bool
	{
		return in_array($method, $this->methods);
	}

	/**
	 * Returns TRUE if the route has a trailing slash and FALSE if not.
	 *
	 * @return bool
	 */
	public function hasTrailingSlash(): bool
	{
		return $this->hasTrailingSlash;
	}

	/**
	 * Returns the regex needed to match the route.
	 *
	 * @return string
	 */
	public function getRegex(): string
	{
		$route = $this->getRoute();

		if(strpos($route, '?'))
		{
			$route = preg_replace('/\/{(\w+)}\?/', '(?:/{$1})?', $route);
		}

		if(!empty($this->patterns))
		{
			foreach($this->patterns as $key => $pattern)
			{
				$route = str_replace("{{$key}}", "(?P<{$key}>{$pattern})", $route);
			}
		}

		$route = preg_replace('/{((\d*[a-z_]\d*)+)}/i', '(?P<$1>[^/]++)', $route);

		if($this->hasTrailingSlash)
		{
			$route .= '?';
		}

		return "#^{$route}$#su";
	}
}
