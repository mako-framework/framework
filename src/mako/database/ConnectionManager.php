<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database;

use RuntimeException;

use mako\common\ConnectionManager as BaseConnectionManager;
use mako\database\Connection;

/**
 * Database connection manager.
 *
 * @author  Frederic G. Østby
 *
 * @method  \mako\database\Connection  connection($connection = null)
 */

class ConnectionManager extends BaseConnectionManager
{
	/**
	 * Connects to the chosen database and returns the connection.
	 *
	 * @access  public
	 * @param   string                     $connection  Connection name
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
	 * @param   boolean  $groupedByConnection  Group logs by connection?
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