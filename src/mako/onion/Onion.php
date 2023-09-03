<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\onion;

use Closure;
use mako\onion\exceptions\OnionException;
use mako\syringe\Container;

use function array_push;
use function array_unshift;
use function vsprintf;

/**
 * Middleware stack.
 */
class Onion
{
	/**
	 * Middleware layers.
	 */
	protected array $layers = [];

	/**
	 * Middleware parameters.
	 */
	protected array $parameters = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Container $container = new Container,
		protected string $method = 'handle',
		protected ?string $expectedInterface = null
	)
	{}

	/**
	 * Add a new middleware layer.
	 */
	public function addLayer(string $class, ?array $parameters = null, bool $inner = true): int
	{
		$this->parameters[$class] = $parameters;

		return $inner ? array_unshift($this->layers, $class) : array_push($this->layers, $class);
	}

	/**
	 * Add a inner layer to the middleware stack.
	 */
	public function addInnerLayer(string $class, ?array $parameters = null): int
	{
		return $this->addLayer($class, $parameters);
	}

	/**
	 * Add an outer layer to the middleware stack.
	 */
	public function addOuterLayer(string $class, ?array $parameters = null): int
	{
		return $this->addLayer($class, $parameters, false);
	}

	/**
	 * Builds the core closure.
	 */
	protected function buildCoreClosure(object $object): Closure
	{
		return function (...$arguments) use ($object)
		{
			$callable = $object instanceof Closure ? $object : [$object, $this->method];

			return $callable(...$arguments);
		};
	}

	/**
	 * Builds a layer closure.
	 */
	protected function buildLayerClosure(object $layer, Closure $next): Closure
	{
		return fn (...$arguments) => $layer->execute(...[...$arguments, $next]);
	}

	/**
	 * Returns the parameters of the requested middleware.
	 */
	protected function mergeParameters(array $parameters, string $middleware): array
	{
		return ($parameters[$middleware] ?? []) + ($this->parameters[$middleware] ?? []);
	}

	/**
	 * Middleware factory.
	 */
	protected function middlewareFactory(string $middleware, array $parameters): object
	{
		// Merge middleware parameters

		$parameters = $this->mergeParameters($parameters, $middleware);

		// Create middleware instance

		$middleware = $this->container->get($middleware, $parameters);

		// Check if the middleware implements the expected interface

		if($this->expectedInterface !== null && ($middleware instanceof $this->expectedInterface) === false)
		{
			throw new OnionException(vsprintf('The Onion instance expects the middleware to be an instance of [ %s ].', [$this->expectedInterface]));
		}

		// Return middleware instance

		return $middleware;
	}

	/**
	 * Executes the middleware stack.
	 */
	public function peel(object $object, array $parameters = [], array $middlewareParameters = []): mixed
	{
		$next = $this->buildCoreClosure($object);

		foreach($this->layers as $layer)
		{
			$middleware = $this->middlewareFactory($layer, $middlewareParameters);

			$next = $this->buildLayerClosure($middleware, $next);
		}

		return $next(...$parameters);
	}
}
