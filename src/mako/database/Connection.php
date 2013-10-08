<?php

namespace mako\database;

use \PDO;
use \Closure;
use \PDOException;
use \RuntimeException;
use \mako\core\Config;
use \mako\database\Database;
use \mako\database\query\Query;

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

	protected $pdo;

	/**
	 * Driver name.
	 * 
	 * @var string
	 */

	protected $driver;

	/**
	 * Compiler name.
	 * 
	 * @var string
	 */

	protected $compiler;

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

		$this->driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

		// Set the compiler name

		$this->compiler = isset($config['compiler']) ? $config['compiler'] : $this->driver;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the PDO instance.
	 * 
	 * @access  public
	 * @return  \PDO
	 */

	public function getPDO()
	{
		return $this->pdo;
	}

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
	 * Returns the compiler name.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getCompiler()
	{
		return $this->compiler;
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
	 * Prepares a query.
	 * 
	 * @access  protected
	 * @param   string     $query   SQL query
	 * @param   array      $params  Query parameters
	 * @return  array
	 */

	protected function prepare($query, array $params)
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

		// Return query, parameters and the prepared statement

		return array('query' => $query, 'params' => $params, 'statement' => $this->pdo->prepare($query));
	}

	/**
	 * Executes the prepared query and returns TRUE on success or FALSE on failure.
	 *
	 * @access  protected
	 * @param   array      $prepared  Prepared query
	 * @return  boolean
	 */

	protected function execute(array $prepared)
	{
		if($this->enableLog)
		{
			$start = microtime(true);
		}

		$result = $prepared['statement']->execute($prepared['params']);

		if($this->enableLog)
		{
			$this->log($prepared['query'], $prepared['params'], $start);
		}

		return $result;
	}

	/**
	 * Executes the query and returns TRUE on success or FALSE on failure.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  (optional) Query parameters
	 * @return  mixed
	 */

	public function query($query, array $params = array())
	{
		return $this->execute($this->prepare($query, $params));
	}

	/**
	 * Executes the query and returns TRUE on success or FALSE on failure.
	 *
	 * @access  public
	 * @param   string   $query   SQL query
	 * @param   array    $params  (optional) Query parameters
	 * @return  boolean
	 */

	public function insert($query, array $params = array())
	{
		return $this->query($query, $params);
	}

	/**
	 * Returns an array containing all of the result set rows.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  (optional) Query parameters
	 * @return  array
	 */

	public function all($query, array $params = array())
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		return $prepared['statement']->fetchAll();
	}

	/**
	 * Returns the first row of the result set.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  (optional) Query params
	 * @return  mixed
	 */

	public function first($query, array $params = array())
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		return $prepared['statement']->fetch();
	}

	/**
	 * Returns the value of the first column of the first row of the result set.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  (optional) Query parameters
	 * @return  mixed
	 */

	public function column($query, array $params = array())
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		return $prepared['statement']->fetchColumn();
	}

	/**
	 * Executes the query and return number of affected rows.
	 * 
	 * @access  protected
	 * @param   string     $query   SQL query
	 * @param   array      $params  (optional) Query parameters
	 * @return  int
	 */

	protected function executeAndCount($query, array $params)
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		return $prepared['statement']->rowCount();
	}

	/**
	 * Executes the query and returns the number of updated records.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  (optional) Query parameters
	 * @return  int
	 */

	public function update($query, array $params = array())
	{
		return $this->executeAndCount($query, $params);
	}

	/**
	 * Executes the query and returns the number of deleted records.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  (optional) Query parameters
	 * @return  int
	 */

	public function delete($query, array $params = array())
	{
		return $this->executeAndCount($query, $params);
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @access  public
	 * @param   mixed                       $table  Table name or subquery
	 * @return  \mako\database\query\Query
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
	 * @return  mixed
	 */

	public function transaction(Closure $queries)
	{
		try
		{
			$this->pdo->beginTransaction();

			$result = $queries($this);

			$this->pdo->commit();
		}
		catch(PDOException $e)
		{
			$this->pdo->rollBack();

			throw $e;
		}

		return $result;
	}
}

/** -------------------- End of file -------------------- **/