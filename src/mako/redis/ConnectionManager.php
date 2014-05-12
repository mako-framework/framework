<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\redis;

use \RuntimeException;

use \mako\redis\Redis;

/**
 * Redis connection manager.
 *
 * @author  Frederic G. Østby
 */

class ConnectionManager extends \mako\common\ConnectionManager
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

		return new Redis($this->configurations[$connection]);
	}
}