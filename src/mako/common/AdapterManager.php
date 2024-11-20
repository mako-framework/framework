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
use function sprintf;

/**
 * Adapter manager.
 */
abstract class AdapterManager
{
	use ConfigurableTrait;

	/**
	 * Extensions.
	 */
	protected array $extensions = [];

	/**
	 * Connections.
	 */
	protected array $instances = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $default,
		protected array $configurations,
		protected Container $container
	) {
	}

	/**
	 * Adds extension.
	 */
	public function extend(string $name, Closure|string $adapter): void
	{
		$this->extensions[$name] = $adapter;
	}

	/**
	 * Factory.
	 */
	protected function factory(string $adapterName, array $configuration = []): mixed
	{
		if (method_exists($this, ($method = "{$adapterName}Factory"))) {
			return $this->{$method}($configuration);
		}
		elseif (isset($this->extensions[$adapterName])) {
			$adapter = $this->extensions[$adapterName];

			if ($adapter instanceof Closure) {
				return $this->container->call($adapter, $configuration);
			}

			return $this->container->get($adapter, $configuration);
		}

		throw new AdapterManagerException(sprintf('A factory method for the [ %s ] adapter has not been defined.', $adapterName));
	}

	/**
	 * Returns a new adapter instance.
	 */
	abstract protected function instantiate(string $configuration): mixed;

	/**
	 * Returns an instance of the chosen adapter configuration.
	 */
	public function getInstance(?string $configuration = null): mixed
	{
		$configuration ??= $this->default;

		if (!isset($this->instances[$configuration])) {
			$this->instances[$configuration] = $this->instantiate($configuration);
		}

		return $this->instances[$configuration];
	}

	/**
	 * Magic shortcut to the default configuration.
	 */
	public function __call(string $name, array $arguments): mixed
	{
		return $this->getInstance()->{$name}(...$arguments);
	}
}
