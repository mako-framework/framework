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
use function str_contains;
use function str_replace;
use function substr;
use function trim;

/**
 * Route.
 */
class Route
{
	/**
	 * Route prefix.
	 */
	protected string $prefix = '';

	/**
	 * Does the route have a trailing slash?
	 */
	protected bool $hasTrailingSlash;

	/**
	 * Route patterns.
	 */
	protected array $patterns = [];

	/**
	 * Middleware.
	 */
	protected array $middleware = [];

	/**
	 * Constraints.
	 */
	protected array $constraints = [];

	/**
	 * Parameters.
	 */
	protected array $parameters = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected array $methods,
		protected string $route,
		protected array|Closure|string $action,
		protected ?string $name = null
	) {
		$this->hasTrailingSlash = (substr($this->route, -1) === '/');
	}

	/**
	 * Returns the HTTP methods the route responds to.
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

	/**
	 * Returns the route.
	 */
	public function getRoute(): string
	{
		return "{$this->prefix}{$this->route}";
	}

	/**
	 * Returns the route action.
	 */
	public function getAction(): array|Closure|string
	{
		return $this->action;
	}

	/**
	 * Returns the route name.
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * Returns attribute values.
	 */
	protected function getAttributeValues(array $attributes, string $method): array
	{
		$attributeValues = [];

		/** @var \ReflectionAttribute $attribute */
		foreach ($attributes as $attribute) {
			$attributeValues = [...$attributeValues, ...[$attribute->newInstance()->{$method}()]];
		}

		return $attributeValues;
	}

	/**
	 * Returns the middleware.
	 */
	public function getMiddleware(): array
	{
		if ($this->action instanceof Closure) {
			return $this->middleware;
		}

		[$class, $method] = is_array($this->action) ? $this->action : [$this->action, '__invoke'];

		return [
			...$this->middleware,
			...$this->getAttributeValues((new ReflectionClass($class))->getAttributes(Middleware::class), 'getMiddlewareAndParameters'),
			...$this->getAttributeValues((new ReflectionMethod($class, $method))->getAttributes(Middleware::class), 'getMiddlewareAndParameters'),
		];
	}

	/**
	 * Returns the constraints.
	 */
	public function getConstraints(): array
	{
		if ($this->action instanceof Closure) {
			return $this->constraints;
		}

		[$class, $method] = is_array($this->action) ? $this->action : [$this->action, '__invoke'];

		return [
			...$this->constraints,
			...$this->getAttributeValues((new ReflectionClass($class))->getAttributes(Constraint::class), 'getConstraintAndParameters'),
			...$this->getAttributeValues((new ReflectionMethod($class, $method))->getAttributes(Constraint::class), 'getConstraintAndParameters'),
		];
	}

	/**
	 * Sets the route parameters.
	 */
	public function setParameters(array $parameters): void
	{
		$this->parameters = $parameters;
	}

	/**
	 * Returns all the route parameters.
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * Returns the named parameter value.
	 */
	public function getParameter(string $name, mixed $default = null): mixed
	{
		return $this->parameters[$name] ?? $default;
	}

	/**
	 * Adds a prefix to the route.
	 *
	 * @return $this
	 */
	public function prefix(string $prefix): Route
	{
		if (!empty($prefix)) {
			$this->prefix .= '/' . trim($prefix, '/');
		}

		return $this;
	}

	/**
	 * Sets the custom patterns.
	 *
	 * @return $this
	 */
	public function patterns(array $patterns): Route
	{
		$this->patterns = $patterns + $this->patterns;

		return $this;
	}

	/**
	 * Adds a middleware.
	 *
	 * @return $this
	 */
	public function middleware(string $middleware, mixed ...$parameters): Route
	{
		$this->middleware = [...$this->middleware, ...[['middleware' => $middleware, 'parameters' => $parameters]]];

		return $this;
	}

	/**
	 * Adds a constraint.
	 *
	 * @return $this
	 */
	public function constraint(string $constraint, mixed ...$parameters): Route
	{
		$this->constraints = [...$this->constraints, ...[['constraint' => $constraint, 'parameters' => $parameters]]];

		return $this;
	}

	/**
	 * Returns TRUE if the route allows the specified method or FALSE if not.
	 */
	public function allowsMethod(string $method): bool
	{
		return in_array($method, $this->methods);
	}

	/**
	 * Returns TRUE if the route has a trailing slash and FALSE if not.
	 */
	public function hasTrailingSlash(): bool
	{
		return $this->hasTrailingSlash;
	}

	/**
	 * Returns the regex needed to match the route.
	 */
	public function getRegex(): string
	{
		$route = $this->getRoute();

		if (str_contains($route, '?')) {
			$route = preg_replace('/\/{(\w+)}\?/', '(?:/{$1})?', $route);
		}

		if (!empty($this->patterns)) {
			foreach ($this->patterns as $key => $pattern) {
				$route = str_replace("{{$key}}", "(?P<{$key}>{$pattern})", $route);
			}
		}

		$route = preg_replace('/{((\d*[a-z_]\d*)+)}/i', '(?P<$1>[^/]++)', $route);

		if ($this->hasTrailingSlash) {
			$route .= '?';
		}

		return "#^{$route}$#su";
	}
}
