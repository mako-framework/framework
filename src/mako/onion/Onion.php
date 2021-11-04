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
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Method to call on the decoracted class.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Expected middleware interface.
	 *
	 * @var string|null
	 */
	protected $expectedInterface;

	/**
	 * Middleware layers.
	 *
	 * @var array
	 */
	protected $layers = [];

	/**
	 * Middleware parameters.
	 *
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * Constructor.
	 *
	 * @param \mako\syringe\Container|null $container         Container
	 * @param string|null                  $method            Method to call on the decoracted class
	 * @param string|null                  $expectedInterface Expected middleware interface
	 */
	public function __construct(?Container $container = null, ?string $method = null, ?string $expectedInterface = null)
	{
		$this->container = $container ?? new Container;

		$this->method = $method ?? 'handle';

		$this->expectedInterface = $expectedInterface;
	}

	/**
	 * Add a new middleware layer.
	 *
	 * @param  string     $class      Class
	 * @param  array|null $parameters Middleware parameters
	 * @param  bool       $inner      Add an inner layer?
	 * @return int
	 */
	public function addLayer(string $class, ?array $parameters = null, bool $inner = true): int
	{
		$this->parameters[$class] = $parameters;

		return $inner ? array_unshift($this->layers, $class) : array_push($this->layers, $class);
	}

	/**
	 * Add a inner layer to the middleware stack.
	 *
	 * @param  string     $class      Class
	 * @param  array|null $parameters Middleware parameters
	 * @return int
	 */
	public function addInnerLayer(string $class, ?array $parameters = null): int
	{
		return $this->addLayer($class, $parameters);
	}

	/**
	 * Add an outer layer to the middleware stack.
	 *
	 * @param  string     $class      Class
	 * @param  array|null $parameters Middleware parameters
	 * @return int
	 */
	public function addOuterLayer(string $class, ?array $parameters = null): int
	{
		return $this->addLayer($class, $parameters, false);
	}

	/**
	 * Builds the core closure.
	 *
	 * @param  object   $object The object that we're decorating
	 * @return \Closure
	 */
	protected function buildCoreClosure(object $object): Closure
	{
		return function(...$arguments) use ($object)
		{
			$callable = $object instanceof Closure ? $object : [$object, $this->method];

			return $callable(...$arguments);
		};
	}

	/**
	 * Builds a layer closure.
	 *
	 * @param  object   $layer Middleware object
	 * @param  \Closure $next  The next middleware layer
	 * @return \Closure
	 */
	protected function buildLayerClosure(object $layer, Closure $next): Closure
	{
		return fn(...$arguments) => $layer->execute(...[...$arguments, $next]);
	}

	/**
	 * Returns the parameters of the requested middleware.
	 *
	 * @param  array  $parameters Parameters array
	 * @param  string $middleware Middleware name
	 * @return array
	 */
	protected function mergeParameters(array $parameters, string $middleware): array
	{
		return ($parameters[$middleware] ?? []) + ($this->parameters[$middleware] ?? []);
	}

	/**
	 * Middleware factory.
	 *
	 * @param  string $middleware Middleware class name
	 * @param  array  $parameters Middleware parameters
	 * @return object
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
	 *
	 * @param  object $object               The object that we're decorating
	 * @param  array  $parameters           Parameters
	 * @param  array  $middlewareParameters Middleware parameters
	 * @return mixed
	 */
	public function peel(object $object, array $parameters = [], array $middlewareParameters = [])
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
