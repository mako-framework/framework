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
	 * Constructor.
	 *
	 * @access  public
	 * @param   null|mako\syringe\Container  $container  Container
	 * @param   null|string                  $method     Method to call on the decoracted class
	 */

	public function __construct(Container $container = null, $method = null)
	{
		$this->container = $container ?: new Container;

		$this->method = $method ?: 'handle';
	}

	/**
	 * Add a new middleware layer.
	 *
	 * @access  public
	 * @param   string   $class  Class
	 * @param   boolean  $inner  Add an inner layer?
	 * @return  int
	 */

	public function addLayer($class, $inner = true)
	{
		return $inner ? array_unshift($this->layers, $class) : array_push($this->layers, $class);
	}

	/**
	 * Add a inner layer to the middleware stack.
	 *
	 * @access  public
	 * @param   string  $class  Class
	 * @return  int
	 */

	public function addInnerLayer($class)
	{
		return $this->addLayer($class);
	}

	/**
	 * Add an outer layer to the middleware stack.
	 *
	 * @access  public
	 * @param   string  $class  Class
	 * @return  int
	 */

	public function addOuterLayer($class)
	{
		return $this->addLayer($class, false);
	}

	/**
	 * Builds the core closure.
	 *
	 * @access  protected
	 * @param   object     $object  The object that we're decorating
	 * @return  \Closure
	 */

	protected function buildCoreClosure($object)
	{
		return function() use ($object)
		{
			$callable = $object instanceof Closure ? $object : [$object, $this->method];

			return call_user_func_array($callable, func_get_args());
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

	protected function buildLayerClosure($layer, Closure $next)
	{
		return function() use ($layer, $next)
		{
			return call_user_func_array([$layer, 'execute'], array_merge(func_get_args(), [$next]));
		};
	}

	/**
	 * Executes the middleware stack.
	 *
	 * @access  public
	 * @param   object  $object       The object that we're decorating
	 * @param   array   $parameters   Parameters
	 * @return  mixed
	 */

	public function peel($object, array $parameters = [])
	{
		$next = $this->buildCoreClosure($object);

		foreach($this->layers as $layer)
		{
			$layer = $this->container->get($layer);

			$next = $this->buildLayerClosure($layer, $next);
		}

		return call_user_func_array($next, $parameters);
	}
}