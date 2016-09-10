<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\onion;

use Closure;

use mako\onion\Core;
use mako\syringe\Container;

/**
 * Middleware stack.
 *
 * @author Yamada Taro
 */
class Onion
{
	/**
	 * Method to call on the decoracted class.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Middleware layers.
	 *
	 * @var array
	 */
	protected $layers = [];

	/**
	 * Middleware constructor parameters.
	 *
	 * @var array
	 */
	protected $middlewareConstructorParameters = [];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   null|\mako\syringe\Container  $container  Container
	 * @param   null|string                   $method     Method to call on the decoracted class
	 */
	public function __construct(Container $container = null, string $method = null)
	{
		$this->container = $container ?? new Container;

		$this->method = $method ?? 'handle';
	}

	/**
	 * Add a new middleware layer.
	 *
	 * @access  public
	 * @param   string      $class       Class
	 * @param   null|array  $parameters  Constructor parameters
	 * @param   bool        $inner       Add an inner layer?
	 * @return  int
	 */
	public function addLayer(string $class, array $parameters = null, bool $inner = true): int
	{
		$this->middlewareConstructorParameters[$class] = $parameters;

		return $inner ? array_unshift($this->layers, $class) : array_push($this->layers, $class);
	}

	/**
	 * Add a inner layer to the middleware stack.
	 *
	 * @access  public
	 * @param   string      $class  Class
	 * @param   null|array  $parameters  Constructor parameters
	 * @return  int
	 */
	public function addInnerLayer(string $class, array $parameters = null): int
	{
		return $this->addLayer($class, $parameters);
	}

	/**
	 * Add an outer layer to the middleware stack.
	 *
	 * @access  public
	 * @param   string      $class  Class
	 * @param   null|array  $parameters  Constructor parameters
	 * @return  int
	 */
	public function addOuterLayer(string $class, array $parameters = null): int
	{
		return $this->addLayer($class, $parameters, false);
	}

	/**
	 * Builds the core closure.
	 *
	 * @access  protected
	 * @param   object     $object  The object that we're decorating
	 * @return  \Closure
	 */
	protected function buildCoreClosure($object): Closure
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
	 * @access  protected
	 * @param   object     $layer  Middleware object
	 * @param   \Closure   $next   The next middleware layer
	 * @return  \Closure
	 */
	protected function buildLayerClosure($layer, Closure $next): Closure
	{
		return function(...$arguments) use ($layer, $next)
		{
			return $layer->execute(...array_merge($arguments, [$next]));
		};
	}

	/**
	 * Returns the constructor parameters of the requested middleware.
	 *
	 * @access  protected
	 * @param   array      $parameters  Parameters array
	 * @param   string     $middleware  Middleware name
	 * @return  array
	 */
	protected function getMiddlewareParameters(array $parameters, string $middleware): array
	{
		return ($parameters[$middleware] ?? []) + ($this->middlewareConstructorParameters[$middleware] ?? []);
	}

	/**
	 * Executes the middleware stack.
	 *
	 * @access  public
	 * @param   object  $object                The object that we're decorating
	 * @param   array   $parameters            Parameters
	 * @param   array   $middlewareParameters  Middleware constructor parameters
	 * @return  mixed
	 */
	public function peel($object, array $parameters = [], array $middlewareParameters = [])
	{
		$next = $this->buildCoreClosure($object);

		foreach($this->layers as $layer)
		{
			$layer = $this->container->get($layer, $this->getMiddlewareParameters($middlewareParameters, $layer));

			$next = $this->buildLayerClosure($layer, $next);
		}

		return $next(...$parameters);
	}
}