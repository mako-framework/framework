<?php

namespace mako;

use \mako\Config;
use \PDO;
use \PDOException;
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
	* Holds instance of itself.
	*
	* @var mako\Database
	*/

	protected static $instance = null;

	/**
	* Holds the configuration.
	*
	* @var array
	*/

	protected static $config;

	/**
	* Holds all the database objects.
	*
	* @var array
	*/

	protected static $connections = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Protected constructor that prevents direct creation of object.
	*
	* @access  protected
	*/

	protected function __construct()
	{
		// Nothing here
	}

	/**
	* Closes all database connections.
	*
	* @access  public
	*/

	public function __destruct()
	{
		foreach(static::$connections as $k => $v)
		{
			static::close($k);
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------
	
	/**
	* Opens a new connection or returns existing connection if it already exists.
	*
	* @access  public
	* @param   string  (optional) Database configuration name
	* @return  PDO
	*/

	public static function instance($name = null)
	{
		if(static::$instance === null)
		{
			static::$instance = new static();
			
			static::$config = Config::get('database');
		}
		
		if(isset(static::$connections[$name]))
		{
			return static::$connections[$name];
		}
		else
		{
			$name = ($name === null) ? static::$config['default'] : $name;
			
			if(isset(static::$config['configurations'][$name]) === false)
			{
				throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the database configuration.", array(__METHOD__, $name)));
			}
			
			static::connect($name);
			
			return static::$connections[$name];
		}
	}

	/**
	* Connect to database using PDO.
	*
	* @access  protected
	* @param   string     Database name as defined in the database config file
	*/

	protected static function connect($name)
	{
		$config = static::$config['configurations'][$name];

		// Connect to the database

		$user = isset($config['username']) ? $config['username'] : null;
		$pass = isset($config['password']) ? $config['password'] : null;

		$options = array
		(
			PDO::ATTR_PERSISTENT         => isset($config['persistent']) ? $config['persistent'] : false,
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
		);

		try
		{
			static::$connections[$name] = new PDO($config['dsn'], $user, $pass, $options);
		}
		catch(PDOException $e)
		{
			throw new RuntimeException(vsprintf("%s(): Failed to connect to the '%s' database. %s", array(__METHOD__, $name, $e->getMessage())));
		}

		// Run queries

		if(isset($config['queries']))
		{
			foreach($config['queries'] as $query)
			{
				static::$connections[$name]->exec($query);
			}
		}
	}

	/**
	* Closes the connection to a database and destroys the object.
	*
	* @access  public
	* @param   string   Database name as defined in the database config file.
	* @return  boolean
	*/

	public static function close($name = null)
	{
		$name = ($name === null) ? static::$config['default'] : $name;
		
		if(isset(static::$connections[$name]))
		{
			static::$connections[$name] = null;

			unset(static::$connections[$name]);

			return true;
		}
		else
		{
			return false;
		}
	}
}

/** -------------------- End of file --------------------**/