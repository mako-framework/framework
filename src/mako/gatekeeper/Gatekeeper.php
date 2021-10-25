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
	 * Status code for banned users.
	 *
	 * @var int
	 */
	public const LOGIN_BANNED = 100;

	/**
	 * Status code for users who need to activate their account.
	 *
	 * @var int
	 */
	public const LOGIN_ACTIVATING = 101;

	/**
	 * Status code for users who fail to provide the correct credentials.
	 *
	 * @var int
	 */
	public const LOGIN_INCORRECT = 102;

	/**
	 * Status code for users that are temporarily locked.
	 *
	 * @var int
	 */
	public const LOGIN_LOCKED = 103;

	/**
	 * Default adapter name.
	 *
	 * @var string
	 */
	protected $defaultAdapter;

	/**
	 * Adapters.
	 *
	 * @var array
	 */
	protected $adapters = [];

	/**
	 * Adapter factories.
	 *
	 * @var array
	 */
	protected $adapterFactories = [];

	/**
	 * Constructor.
	 *
	 * @param array|\mako\gatekeeper\adapters\AdapterInterface $adapter Array containing the adapter name and closure factory or an adapter instance
	 */
	public function __construct($adapter)
	{
		$this->defaultAdapter = $this->registerAdapter($adapter);
	}

	/**
	 * Registers an adapter instance.
	 *
	 * @param  \mako\gatekeeper\adapters\AdapterInterface $adapter Adapter instance
	 * @return string
	 */
	protected function registerAdapterInstance(AdapterInterface $adapter): string
	{
		$this->adapters[$name = $adapter->getName()] = $adapter;

		return $name;
	}

	/**
	 * Registers an adapter factory.
	 *
	 * @param  string   $name    Adapter name
	 * @param  \Closure $factory Adapter factory
	 * @return string
	 */
	protected function registerAdapterFactory(string $name, Closure $factory): string
	{
		$this->adapterFactories[$name] = $factory;

		return $name;
	}

	/**
	 * Registers an adapter.
	 *
	 * @param  array|\mako\gatekeeper\adapters\AdapterInterface $adapter Array containing the adapter name and closure factory or an adapter instance
	 * @return string
	 */
	protected function registerAdapter($adapter): string
	{
		if($adapter instanceof AdapterInterface)
		{
			return $this->registerAdapterInstance($adapter);
		}

		[$name, $factory] = $adapter;

		return $this->registerAdapterFactory($name, $factory);
	}

	/**
	 * Registers a new adapter.
	 *
	 * @param  array|\mako\gatekeeper\adapters\AdapterInterface $adapter Array containing the adapter name and closure factory or an adapter instance
	 * @return \mako\gatekeeper\Gatekeeper
	 */
	public function extend($adapter): Gatekeeper
	{
		$this->registerAdapter($adapter);

		return $this;
	}

	/**
	 * Sets the defaut adapter name.
	 *
	 * @param  string                      $name Adapter name
	 * @return \mako\gatekeeper\Gatekeeper
	 */
	public function useAsDefaultAdapter(string $name): Gatekeeper
	{
		$this->defaultAdapter = $name;

		return $this;
	}

	/**
	 * Creates an adapter instance using a factory.
	 *
	 * @param  string                                     $name Adapter name
	 * @return \mako\gatekeeper\adapters\AdapterInterface
	 */
	protected function adapterFactory(string $name): AdapterInterface
	{
		$factory = $this->adapterFactories[$name];

		return $this->adapters[$name] = $factory();
	}

	/**
	 * Returns an adapter instance.
	 *
	 * @param  string|null                                $name Adapter name
	 * @return \mako\gatekeeper\adapters\AdapterInterface
	 */
	public function adapter(?string $name = null): AdapterInterface
	{
		$name ??= $this->defaultAdapter;

		return $this->adapters[$name] ?? $this->adapterFactory($name);
	}

	/**
	 * Magic shortcut to the default adapter.
	 *
	 * @param  string $name      Method name
	 * @param  array  $arguments Method arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments)
	{
		return $this->adapter()->$name(...$arguments);
	}
}
