<?php

namespace mako\database;

use \PDO;
use \Closure;
use \PDOException;
use \RuntimeException;
use \mako\Config;
use \mako\Database;
use \mako\database\Query;

/**
 * Database connection.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Connection
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * PDO object.
	 *
	 * @var \PDO
	 */

	public $pdo;

	/**
	 * Driver name.
	 * 
	 * @var string
	 */

	protected $driver;

	/**
	 * Enable the query log?
	 *
	 * @var boolean
	 */

	protected $enableLog;

	/**
	 * Query log.
	 *
	 * @var array
	 */

	protected $log = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string   $name       Connection name
	 * @param   array    $config     Connection configuration
	 * @param   boolean  $enableLog  Enable the query log?
	 */

	public function __construct($name, array $config, $enableLog)
	{
		$this->enableLog = $enableLog;

		// Connect to the database

		$user = isset($config['username']) ? $config['username'] : null;
		$pass = isset($config['password']) ? $config['password'] : null;

		$options = array
		(
			PDO::ATTR_PERSISTENT         => isset($config['persistent']) ? $config['persistent'] : false,
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::ATTR_STRINGIFY_FETCHES  => false,
			PDO::ATTR_EMULATE_PREPARES   => false,
		);

		try
		{
			$this->pdo = new PDO($config['dsn'], $user, $pass, $options);
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
				$this->pdo->exec($query);
			}
		}

		// Set the driver name

		$this->driver = isset($config['driver']) ? $config['driver'] : $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the driver name.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getDriver()
	{
		return $this->driver;
	}

	/**
	 * Enables the query log.
	 * 
	 * @access  public
	 */

	public function enableLog()
	{
		$this->enableLog = true;
	}

	/**
	 * Disables the query log.
	 * 
	 * @access  public
	 */

	public function disableLog()
	{
		$this->enableLog = false;
	}

	/**
	 * Adds a query to the query log.
	 *
	 * @access  protected
	 * @param   string     $query   SQL query
	 * @param   array      $params  Query parameters
	 * @param   int        $start   Start time in microseconds
	 */

	protected function log($query, $params, $start)
	{
		$pdo = $this->pdo;

		$time = microtime(true) - $start;

		$query = preg_replace_callback('/\?/', function($matches) use (&$params, $pdo)
		{
			$param = array_shift($params);

			return (is_int($param) || is_float($param)) ? $param : $pdo->quote($param);
		}, $query);

		$this->log[] = compact('query', 'time');
	}

	/**
	 * Returns the query log for the connection.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getLog()
	{
		return $this->log;
	}

	/**
	 * Executes a query and returns the results.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  Query parameters
	 * @param   int     $fetch   Fetch mode
	 * @return  mixed
	 */

	public function query($query, array $params = array(), $fetch = Database::FETCH_ALL)
	{
		// Replace IN clause placeholder with escaped values

		if(strpos($query, '([?])') !== false)
		{
			foreach($params as $key => $value)
			{
				if(is_array($value))
				{
					array_splice($params, $key, 1, $value);

					$query = preg_replace('/\(\[\?\]\)/', '(' . trim(str_repeat('?, ', count($value)), ', ') . ')', $query, 1);
				}
			}
		}

		// Prepare and execute query

		$stmt = $this->pdo->prepare($query);

		if($this->enableLog)
		{
			$start = microtime(true);
		}

		$result = $stmt->execute($params);

		if($this->enableLog)
		{
			$this->log($query, $params, $start);
		}

		// Return results for selects, row count for updates and deletes and boolean for the rest

		if(stripos($query, 'select') === 0)
		{
			switch($fetch)
			{
				case Database::FETCH_FIRST:
					return $stmt->fetch();
				break;
				case Database::FETCH_COLUMN:
					return $stmt->fetchColumn();
				break;
				default:
					return $stmt->fetchAll();
			}
		}
		elseif(stripos($query, 'update') === 0 || stripos($query, 'delete') === 0)
		{
			return $stmt->rowCount();
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Returns an array containing all of the result set rows.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  Query parameters
	 * @return  array
	 */

	public function all($query, array $params = array())
	{
		return $this->query($query, $params, Database::FETCH_ALL);
	}

	/**
	 * Returns the first row of the result set.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  Query params
	 * @return  mixed
	 */

	public function first($query, array $params = array())
	{
		return $this->query($query, $params, Database::FETCH_FIRST);
	}

	/**
	 * Returns the value of the first column of the first row of the result set.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  Query parameters
	 * @return  mixed
	 */

	public function column($query, array $params = array())
	{
		return $this->query($query, $params, Database::FETCH_COLUMN);
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @access  public
	 * @param   mixed                 $table  Table name or subquery
	 * @return  \mako\database\Query
	 */

	public function table($table)
	{
		return new Query($this, $table);
	}

	/**
	 * Executes queries and rolls back the transaction if any of them fail.
	 *
	 * @access  public
	 * @param   \Closure  $queries  Queries
	 * @return  boolean
	 */

	public function transaction(Closure $queries)
	{
		try
		{
			$this->pdo->beginTransaction();

			$queries($this);

			$this->pdo->commit();

			return true;
		}
		catch(PDOException $e)
		{
			$this->pdo->rollBack();

			return false;
		}
	}
}

/** -------------------- End of file -------------------- **/