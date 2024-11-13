<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper;

use Closure;
use mako\gatekeeper\adapters\AdapterInterface;

/**
 * Gatekeeper.
 *
 * @mixin \mako\gatekeeper\adapters\AdapterInterface
 */
class Gatekeeper
{
	/**
	 * Default adapter name.
	 */
	protected string $defaultAdapter;

	/**
	 * Adapters.
	 */
	protected array $adapters = [];

	/**
	 * Adapter factories.
	 */
	protected array $adapterFactories = [];

	/**
	 * Constructor.
	 */
	public function __construct(AdapterInterface|array $adapter)
	{
		$this->defaultAdapter = $this->registerAdapter($adapter);
	}

	/**
	 * Registers an adapter instance.
	 */
	protected function registerAdapterInstance(AdapterInterface $adapter): string
	{
		$this->adapters[$name = $adapter->getName()] = $adapter;

		return $name;
	}

	/**
	 * Registers an adapter factory.
	 */
	protected function registerAdapterFactory(string $name, Closure $factory): string
	{
		$this->adapterFactories[$name] = $factory;

		return $name;
	}

	/**
	 * Registers an adapter.
	 */
	protected function registerAdapter(AdapterInterface|array $adapter): string
	{
		if ($adapter instanceof AdapterInterface) {
			return $this->registerAdapterInstance($adapter);
		}

		[$name, $factory] = $adapter;

		return $this->registerAdapterFactory($name, $factory);
	}

	/**
	 * Registers a new adapter.
	 */
	public function extend(AdapterInterface|array $adapter): Gatekeeper
	{
		$this->registerAdapter($adapter);

		return $this;
	}

	/**
	 * Sets the defaut adapter name.
	 */
	public function useAsDefaultAdapter(string $name): Gatekeeper
	{
		$this->defaultAdapter = $name;

		return $this;
	}

	/**
	 * Creates an adapter instance using a factory.
	 */
	protected function adapterFactory(string $name): AdapterInterface
	{
		$factory = $this->adapterFactories[$name];

		return $this->adapters[$name] = $factory();
	}

	/**
	 * Returns an adapter instance.
	 */
	public function adapter(?string $name = null): AdapterInterface
	{
		$name ??= $this->defaultAdapter;

		return $this->adapters[$name] ?? $this->adapterFactory($name);
	}

	/**
	 * Magic shortcut to the default adapter.
	 */
	public function __call(string $name, array $arguments): mixed
	{
		return $this->adapter()->{$name}(...$arguments);
	}
}
