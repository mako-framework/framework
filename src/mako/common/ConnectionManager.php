<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\common;

use Closure;
use mako\common\traits\ConfigurableTrait;

use function method_exists;

/**
 * Connection manager.
 */
abstract class ConnectionManager
{
	use ConfigurableTrait {
		ConfigurableTrait::removeConfiguration as baseRemoveConfiguration;
	}

	/**
	 * Connections.
	 */
	protected array $connections = [];

	/**
	 * Connects to the chosen configuration and returns the connection.
	 */
	abstract protected function connect(string $connection): mixed;

	/**
	 * Returns the chosen connection.
	 */
	public function getConnection(?string $connection = null): mixed
	{
		$connection ??= $this->default;

		if (!isset($this->connections[$connection])) {
			$this->connections[$connection] = $this->connect($connection);
		}

		return $this->connections[$connection];
	}

	/**
	 * Closes the chosen connection.
	 */
	public function close(?string $connection = null): void
	{
		$connection ??= $this->default;

		if (isset($this->connections[$connection])) {
			if (method_exists($this->connections[$connection], 'close')) {
				// Call close on the connection object in case there's a reference
				// to the connection that won't be garbage collected immediately

				$this->connections[$connection]->close();
			}

			unset($this->connections[$connection]);
		}
	}

	/**
	 * Executes the passed closure using the chosen connection before closing it.
	 */
	public function executeAndClose(Closure $closure, ?string $connection = null): mixed
	{
		$returnValue = $closure($this->getConnection($connection));

		$this->close($connection);

		return $returnValue;
	}

	/**
	 * Removes a configuration.
	 * It will also close and remove any active connection linked to the configuration.
	 */
	public function removeConfiguration(string $name): void
	{
		$this->close($name);

		$this->baseRemoveConfiguration($name);
	}

	/**
	 * Magic shortcut to the default connection.
	 */
	public function __call(string $name, array $arguments): mixed
	{
		return $this->getConnection()->$name(...$arguments);
	}
}
