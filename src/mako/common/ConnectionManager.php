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
	 *
	 * @var array
	 */
	protected $connections = [];

	/**
	 * Connects to the chosen configuration and returns the connection.
	 *
	 * @param  string $connection Connection name
	 * @return mixed
	 */
	abstract protected function connect(string $connection): mixed;

	/**
	 * Returns the chosen connection.
	 *
	 * @param  string|null $connection Connection name
	 * @return mixed
	 */
	public function getConnection(?string $connection = null): mixed
	{
		$connection ??= $this->default;

		if(!isset($this->connections[$connection]))
		{
			$this->connections[$connection] = $this->connect($connection);
		}

		return $this->connections[$connection];
	}

	/**
	 * Closes the chosen connection.
	 *
	 * @param string|null $connection Connection name
	 */
	public function close(?string $connection = null): void
	{
		$connection ??= $this->default;

		if(isset($this->connections[$connection]))
		{
			if(method_exists($this->connections[$connection], 'close'))
			{
				// Call close on the connection object in case there's a reference
				// to the connection that won't be garbage collected immediately

				$this->connections[$connection]->close();
			}

			unset($this->connections[$connection]);
		}
	}

	/**
	 * Executes the passed closure using the chosen connection before closing it.
	 *
	 * @param  \Closure    $closure    Closure to execute
	 * @param  string|null $connection Connection name
	 * @return mixed
	 */
	public function executeAndClose(Closure $closure, ?string $connection = null): mixed
	{
		try {
			return $closure($this->getConnection($connection));
		}
		finally {
			$this->close($connection);
		}
	}

	/**
	 * Removes a configuration.
	 * It will also close and remove any active connection linked to the configuration.
	 *
	 * @param string $name Connection name
	 */
	public function removeConfiguration(string $name): void
	{
		$this->close($name);

		$this->baseRemoveConfiguration($name);
	}

	/**
	 * Magic shortcut to the default connection.
	 *
	 * @param  string $name      Method name
	 * @param  array  $arguments Method arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments): mixed
	{
		return $this->getConnection()->$name(...$arguments);
	}
}
