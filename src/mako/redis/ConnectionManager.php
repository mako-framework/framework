<?php

namespace mako\redis;

use \RuntimeException;

use \mako\redis\Redis;

/**
 * Redis connection manager.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class ConnectionManager extends \mako\common\ConnectionManager
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

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

