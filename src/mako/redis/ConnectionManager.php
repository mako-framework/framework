<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis;

use mako\common\ConnectionManager as BaseConnectionManager;
use mako\redis\exceptions\RedisException;

use function vsprintf;

/**
 * Redis connection manager.
 *
 * @mixin \mako\redis\Redis
 * @method \mako\redis\Redis connection($connection = null)
 * @method \mako\redis\Redis getConnection($connection = null)
 */
class ConnectionManager extends BaseConnectionManager
{
	/**
	 * Connects to the chosen redis configuration and returns the connection.
	 */
	protected function connect(string $connection): Redis
	{
		if(!isset($this->configurations[$connection]))
		{
			throw new RedisException(vsprintf('[ %s ] has not been defined in the redis configuration.', [$connection]));
		}

		$config = $this->configurations[$connection];

		$options =
		[
			'name'               => $connection,
			'persistent'         => $config['persistent'] ?? false,
			'connection_timeout' => $config['connection_timeout'] ?? 5,
			'read_write_timeout' => $config['timeout'] ?? 60,
			'tcp_nodelay'        => $config['tcp_nodelay'] ?? true,
		];

		return new Redis(new Connection($config['host'], $config['port'], $options), $config);
	}
}
