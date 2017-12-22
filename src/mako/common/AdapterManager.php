<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\common;

use Closure;
use RuntimeException;

use mako\common\traits\ConfigurableTrait;
use mako\syringe\Container;

/**
 * Adapter manager.
 *
 * @author Frederic G. Østby
 */
abstract class AdapterManager
{
	use ConfigurableTrait;

	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Extensions.
	 *
	 * @var array
	 */
	protected $extensions = [];

	/**
	 * Connections.
	 *
	 * @var array
	 */
	protected $instances = [];

	/**
	 * Constructor.
	 *
	 * @param string                  $default        Default connection name
	 * @param array                   $configurations Configurations
	 * @param \mako\syringe\Container $container      Container
	 */
	public function __construct(string $default, array $configurations, Container $container)
	{
		$this->default = $default;

		$this->configurations = $configurations;

		$this->container = $container;
	}

	/**
	 * Adds extension.
	 *
	 * @param string          $name    Adapter name
	 * @param string|\Closure $adapter Adapter
	 */
	public function extend(string $name, $adapter)
	{
		$this->extensions[$name] = $adapter;
	}

	/**
	 * Factory.
	 *
	 * @param  string $adapterName   Adapter name
	 * @param  array  $configuration Adapter configuration
	 * @return mixed
	 */
	protected function factory(string $adapterName, array $configuration = [])
	{
		if(method_exists($this, ($method = $adapterName . 'Factory')))
		{
			return $this->$method($configuration);
		}
		elseif(isset($this->extensions[$adapterName]))
		{
			$adapter = $this->extensions[$adapterName];

			if($adapter instanceof Closure)
			{
				return $this->container->call($adapter, $configuration);
			}

			return $this->container->get($adapter, $configuration);
		}

		throw new RuntimeException(vsprintf('A factory method for the [ %s ] adapter has not been defined.', [$adapterName]));
	}

	/**
	 * Returns a new adapter instance.
	 *
	 * @param  string $configuration Configuration name
	 * @return mixed
	 */
	abstract protected function instantiate(string $configuration);

	/**
	 * Returns an instance of the chosen adapter configuration.
	 *
	 * @param  string|null $configuration Configuration name
	 * @return mixed
	 */
	public function instance(string $configuration = null)
	{
		$configuration = $configuration ?? $this->default;

		if(!isset($this->instances[$configuration]))
		{
			$this->instances[$configuration] = $this->instantiate($configuration);
		}

		return $this->instances[$configuration];
	}

	/**
	 * Magic shortcut to the default configuration.
	 *
	 * @param  string $name      Method name
	 * @param  array  $arguments Method arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments)
	{
		return $this->instance()->{$name}(...$arguments);
	}
}
