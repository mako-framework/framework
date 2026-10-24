<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis;

use mako\common\ConnectionManager as BaseConnectionManager;
use mako\redis\exceptions\RedisException;
use Override;

use function sprintf;

/**
 * Redis connection manager.
 *
 * @mixin \mako\redis\Redis
 * @method \mako\redis\Redis   connection($connection = null)
 * @method \mako\redis\Redis   getConnection($connection = null)
 * @method \mako\redis\Redis[] getOpenConnections()
 */
class ConnectionManager extends BaseConnectionManager
{
	/**
	 * Connects to the chosen redis configuration and returns the connection.
	 */
	#[Override]
	protected function connect(string $connection): Redis
	{
		if (!isset($this->configurations[$connection])) {
			throw new RedisException(sprintf('[ %s ] has not been defined in the redis configuration.', $connection));
		}

		$config = $this->configurations[$connection];

		$clientOptions = [
			'resp'     => $config['resp'] ?? null,
			'username' => $config['username'] ?? null,
			'password' => $config['password'] ?? null,
			'database' => $config['database'] ?? null,
		];

		$connectionOptions = [
			'name'               => $connection,
			'persistent'         => $config['persistent'] ?? null,
			'connection_timeout' => $config['connection_timeout'] ?? null,
			'read_write_timeout' => $config['timeout'] ?? null,
			'tcp_nodelay'        => $config['tcp_nodelay'] ?? null,
		];

		return new Redis(
			new Connection($config['host'], $config['port'], $connectionOptions),
			$clientOptions
		);
	}
}
