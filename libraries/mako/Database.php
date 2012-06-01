<?php

namespace mako;

use \mako\Config;
use \mako\database\Connection;
use \mako\database\query\Raw;
use \RuntimeException;

/**
* Class that handles database connections.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Database
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Holds all the connection objects.
	*
	* @var array
	*/

	protected static $connections = array();

	/**
	* Fetch all.
	*
	* @var int
	*/

	const FETCH_ALL = 10;

	/**
	* Fetch first.
	*
	* @var int
	*/

	const FETCH_FIRST = 11;

	/**
	* Fetch column.
	*
	* @var int
	*/

	const FETCH_COLUMN = 12;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Protected constructor since this is a static class.
	*
	* @access  protected
	*/

	protected function __construct()
	{
		// Nothing here
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Returns the query log for all connections.
	*
	* @return  array
	*/

	public static function profiler()
	{
		$log = array();

		foreach(static::$connections as $connection)
		{
			$log = array_merge($log, $connection->profiler());
		}

		return $log;
	}
	
	/**
	* Opens a new connection or returns existing connection if it already exists.
	*
	* @access  public
	* @param   string                    (optional) Database configuration name
	* @return  mako\database\Connection
	*/

	public static function connection($name = null)
	{
		$config = Config::get('database');
			
		$name = ($name === null) ? $config['default'] : $name;

		if(!isset(static::$connections[$name]))
		{	
			if(isset($config['configurations'][$name]) === false)
			{
				throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the database configuration.", array(__METHOD__, $name)));
			}
			
			static::$connections[$name] = new Connection($name, $config['configurations'][$name], $config['profiler']);			
		}

		return static::$connections[$name];
	}

	/**
	* Returns PDO instance to maintain backwards compatibility.
	*
	* @deprecated
	* @access  public
	* @param   string  (optional) Database configuration name
	* @return  PDO
	*/

	public static function instance($name = null)
	{
		return static::connection($name)->pdo;
	}

	/**
	* Returns a database raw sql container.
	*
	* @access  public
	* @param   string                   Raw SQL
	* @return  mako\database\query\Raw
	*/

	public static function raw($sql)
	{
		return new Raw($sql);
	}

	/**
	* Magic shortcut to the default database connection.
	*
	* @access  public
	* @param   string  Method name
	* @param   array   Method arguments
	* @return  mixed
	*/

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array(array(static::connection(), $name), $arguments);
	}
}

/** -------------------- End of file --------------------**/