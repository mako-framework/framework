<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\common;

use Closure;
use mako\common\exceptions\AdapterManagerException;
use mako\common\traits\ConfigurableTrait;
use mako\syringe\Container;

use function method_exists;
use function vsprintf;

/**
 * Adapter manager.
 */
abstract class AdapterManager
{
	use ConfigurableTrait;

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
	public function __construct(
		protected string $default,
		protected array $configurations,
		protected Container $container
	)
	{}

	/**
	 * Adds extension.
	 *
	 * @param string          $name    Adapter name
	 * @param \Closure|string $adapter Adapter
	 */
	public function extend(string $name, Closure|string $adapter): void
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
	protected function factory(string $adapterName, array $configuration = []): mixed
	{
		if(method_exists($this, ($method = "{$adapterName}Factory")))
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

		throw new AdapterManagerException(vsprintf('A factory method for the [ %s ] adapter has not been defined.', [$adapterName]));
	}

	/**
	 * Returns a new adapter instance.
	 *
	 * @param  string $configuration Configuration name
	 * @return mixed
	 */
	abstract protected function instantiate(string $configuration): mixed;

	/**
	 * Returns an instance of the chosen adapter configuration.
	 *
	 * @param  string|null $configuration Configuration name
	 * @return mixed
	 */
	public function getInstance(?string $configuration = null): mixed
	{
		$configuration ??= $this->default;

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
	public function __call(string $name, array $arguments): mixed
	{
		return $this->getInstance()->$name(...$arguments);
	}
}
