<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\redis;

use RuntimeException;

use mako\common\ConnectionManager as BaseConnectionManager;
use mako\redis\Connection;
use mako\redis\Redis;

/**
 * Redis connection manager.
 *
 * @author  Frederic G. Østby
 *
 * @method  \mako\redis\Redis  connection($connection = null)
 */

class ConnectionManager extends BaseConnectionManager
{
	/**
	 * Connects to the chosen redis configuration and returns the connection.
	 *
	 * @access  public
	 * @param   string             $connection  Connection name
	 * @return  \mako\redis\Redis
	 */

	protected function connect($connection)
	{
		if(!isset($this->configurations[$connection]))
		{
			throw new RuntimeException(vsprintf("%s(): [ %s ] has not been defined in the redis configuration.", [__METHOD__, $connection]));
		}

		$config = $this->configurations[$connection];

		return new Redis(new Connection($config['host'], $config['port']), $config);
	}
}