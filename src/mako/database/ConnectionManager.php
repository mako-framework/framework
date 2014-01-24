<?php

namespace mako\database;

use \RuntimeException;

use \mako\database\Connection;

/**
 * Class that handles database connections.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class ConnectionManager
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Name of the default connection.
	 * 
	 * @var string
	 */

	protected $default;

	/**
	 * Database configurations.
	 * 
	 * @var array
	 */

	protected $configurations;

	/**
	 * Database connections.
	 * 
	 * @var array
	 */

	protected $connections = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $default         Default connection name
	 * @param   array   $configurations  Database configurations
	 */

	public function __construct($default, array $configurations)
	{
		$this->default = $default;

		$this->configurations = $configurations;
	}

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
	 * Returns the chosen connection.
	 * 
	 * @access  public
	 * @param   string                    $connection  (optional) Connection name
	 * @return  \mako\database\Connection
	 */

	public function connection($connection = null)
	{
		$connection = $connection ?: $this->default;

		if(!isset($this->connections[$connection]))
		{
			$this->connections[$connection] = $this->connect($connection);
		}

		return $this->connections[$connection];
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

	/**
	 * Magic shortcut to the default database connection.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public function __call($name, $arguments)
	{
		return call_user_func_array([$this->connection(), $name], $arguments);
	}
}

/** -------------------- End of file -------------------- **/