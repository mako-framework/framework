<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\common;

use mako\common\traits\ConfigurableTrait;

/**
 * Connection manager.
 *
 * @author Frederic G. Østby
 */
abstract class ConnectionManager
{
	use ConfigurableTrait;

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
	abstract protected function connect(string $connection);

	/**
	 * Returns the chosen connection.
	 *
	 * @param  string|null $connection Connection name
	 * @return mixed
	 */
	public function connection(string $connection = null)
	{
		$connection = $connection ?? $this->default;

		if(!isset($this->connections[$connection]))
		{
			$this->connections[$connection] = $this->connect($connection);
		}

		return $this->connections[$connection];
	}

	/**
	 * Magic shortcut to the default connection.
	 *
	 * @param  string $name      Method name
	 * @param  array  $arguments Method arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments)
	{
		return $this->connection()->{$name}(...$arguments);
	}
}
