<?php

namespace mako\database;

use \mako\core\Config;
use \mako\database\Connection;
use \mako\database\query\Query;
use \mako\database\query\Raw;
use \mako\database\query\Subquery;
use \RuntimeException;

/**
 * Class that handles database connections.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Database
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Holds all the connection objects.
	 *
	 * @var array
	 */

	protected static $connections = array();

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
	 * Opens a new connection or returns existing connection if it already exists.
	 *
	 * @access  public
	 * @param   string                     $name  (optional) Database configuration name
	 * @return  \mako\database\Connection
	 */

	public static function connection($name = null)
	{
		$config = Config::get('database');
			
		$name = $name ?: $config['default'];

		if(!isset(static::$connections[$name]))
		{	
			if(isset($config['configurations'][$name]) === false)
			{
				throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the database configuration.", array(__METHOD__, $name)));
			}
			
			static::$connections[$name] = new Connection($name, $config['configurations'][$name]);			
		}

		return static::$connections[$name];
	}

	/**
	 * Returns the query log for all connections.
	 *
	 * @access  public
	 * @param   boolean  $groupedByConnection  (optional) Group logs by connection?
	 * @return  array
	 */

	public static function getLog($groupedByConnection = false)
	{
		$log = array();

		if($groupedByConnection)
		{
			foreach(static::$connections as $connection)
			{
				$log[$connection->getName()] = $connection->getLog();
			}
		}
		else
		{
			foreach(static::$connections as $connection)
			{
				$log = array_merge($log, $connection->getLog());
			}
		}

		return $log;
	}

	/**
	 * Returns a raw sql container.
	 *
	 * @access  public
	 * @param   string                    $sql  Raw SQL
	 * @return  \mako\database\query\Raw
	 */

	public static function raw($sql)
	{
		return new Raw($sql);
	}

	/**
	 * Returns a subquery container.
	 *
	 * @access  public
	 * @param   \mako\database\query\Query     $query  Subquery
	 * @param   string                         $alias  (optional) Alias
	 * @return  \mako\database\query\Subquery
	 */

	public static function subquery(Query $query, $alias = null)
	{
		return new Subquery($query, $alias);
	}

	/**
	 * Magic shortcut to the default database connection.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array(array(static::connection(), $name), $arguments);
	}
}

/** -------------------- End of file -------------------- **/