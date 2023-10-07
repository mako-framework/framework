<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\common\traits;

/**
 * Configurable trait.
 */
trait ConfigurableTrait
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $default,
		protected array $configurations
	) {
	}

	/**
	 * Adds a configuration.
	 */
	public function addConfiguration(string $name, array $configuration): void
	{
		$this->configurations[$name] = $configuration;
	}

	/**
	 * Removes a configuration.
	 * It will also remove any active connection linked to the configuration.
	 */
	public function removeConfiguration(string $name): void
	{
		unset($this->configurations[$name]);
	}
}
