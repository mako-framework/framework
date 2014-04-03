<?php

namespace mako\database;

use \RuntimeException;

use \mako\database\Connection;

/**
 * Database connection manager.
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
	 * Connects to the chosen database and returns the connection.
	 * 
	 * @access  public
	 * @param   string                    $connection  Connection name
	 * @return  \mako\database\Connection
	 */

	protected function connect($connection)
	{
		if(!isset($this->configurations[$connection]))
		{
			throw new RuntimeException(vsprintf("%s(): [ %s ] has not been defined in the database configuration.", [__METHOD__, $connection]));
		}

		return new Connection($connection, $this->configurations[$connection]);
	}

	/**
	 * Returns the query log for all connections.
	 *
	 * @access  public
	 * @param   boolean  $groupedByConnection  (optional) Group logs by connection?
	 * @return  array
	 */

	public function getLogs($groupedByConnection = true)
	{
		$logs = [];

		if($groupedByConnection)
		{
			foreach($this->connections as $connection)
			{
				$logs[$connection->getName()] = $connection->getLog();
			}
		}
		else
		{
			foreach($this->connections as $connection)
			{
				$logs = array_merge($logs, $connection->getLog());
			}
		}

		return $logs;
	}
}

