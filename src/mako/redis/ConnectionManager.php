<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis;

use mako\common\ConnectionManager as BaseConnectionManager;
use RuntimeException;

/**
 * Redis connection manager.
 *
 * @author Frederic G. Østby
 *
 * @method \mako\redis\Redis connection($connection = null)
 */
class ConnectionManager extends BaseConnectionManager
{
	/**
	 * Connects to the chosen redis configuration and returns the connection.
	 *
	 * @param  string            $connection Connection name
	 * @return \mako\redis\Redis
	 */
	protected function connect(string $connection): Redis
	{
		if(!isset($this->configurations[$connection]))
		{
			throw new RuntimeException(vsprintf('[ %s ] has not been defined in the redis configuration.', [$connection]));
		}

		$config = $this->configurations[$connection];

		return new Redis(new Connection($config['host'], $config['port'], $config['persistent'] ?? false), $config);
	}
}
