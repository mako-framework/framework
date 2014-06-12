<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database;

use \PDO;
use \Closure;
use \PDOException;
use \RuntimeException;
use \mako\database\query\Query;

/**
 * Database connection.
 *
 * @author  Frederic G. Østby
 */

class Connection
{
	/**
	 * Connection name.
	 * 
	 * @var string
	 */

	protected $name;

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
	 * SQL dialect.
	 * 
	 * @var string
	 */

	protected $dialect;

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

	protected $log = [];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $name    Connection name
	 * @param   array   $config  Connection configuration
	 */

	public function __construct($name, array $config)
	{
		$this->name = $name;

		// Enable query log?

		$this->enableLog = isset($config['log_queries']) ? $config['log_queries'] : false;

		// Connect to the database

		$user = isset($config['username']) ? $config['username'] : null;
		$pass = isset($config['password']) ? $config['password'] : null;

		$options = 
		[
			PDO::ATTR_PERSISTENT         => isset($config['persistent']) ? $config['persistent'] : false,
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::ATTR_STRINGIFY_FETCHES  => false,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];

		try
		{
			$this->pdo = new PDO($config['dsn'], $user, $pass, $options);
		}
		catch(PDOException $e)
		{
			throw new RuntimeException(vsprintf("%s(): Failed to connect to the [ %s ] database. %s", [__METHOD__, $this->name, $e->getMessage()]));
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

		// Set the compiler type

		$this->dialect = isset($config['dialect']) ? $config['dialect'] : $this->driver;
	}

	/**
	 * Returns the connection name.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getName()
	{
		return $this->name;
	}

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
	 * Returns the SQL dialect.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getDialect()
	{
		return $this->dialect;
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
	 * Replace placeholders with parameteters.
	 * 
	 * @access  protected
	 * @param   string     $query   SQL query
	 * @param   array      $params  Query paramaters
	 * @return  string
	 */

	protected function replaceParams($query, array $params)
	{
		$pdo = $this->pdo;

		return preg_replace_callback('/\?/', function($matches) use (&$params, $pdo)
		{
			$param = array_shift($params);

			return (is_int($param) || is_float($param)) ? $param : $pdo->quote(is_object($param) ? get_class($param) : $param);
		}, $query);
	}

	/**
	 * Adds a query to the query log.
	 *
	 * @access  protected
	 * @param   string     $query   SQL query
	 * @param   array      $params  Query parameters
	 * @param   int        $start   Start time in microseconds
	 */

	protected function log($query, array $params, $start)
	{
		$time = microtime(true) - $start;

		$query = $this->replaceParams($query, $params);

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

		replace:

		if(strpos($query, '([?])') !== false)
		{
			foreach($params as $key => $value)
			{
				if(is_array($value))
				{
					array_splice($params, $key, 1, $value);

					$query = preg_replace('/\(\[\?\]\)/', '(' . trim(str_repeat('?, ', count($value)), ', ') . ')', $query, 1);
					
					goto replace;
				}
			}
		}

		// Prepare statement

		try
		{
			$statement = $this->pdo->prepare($query);
		}
		catch(PDOException $e)
		{
			throw new PDOException($e->getMessage() . ' [ ' . $this->replaceParams($query, $params) . ' ] ', (int) $e->getCode(), $e->getPrevious());
		}

		// Return query, parameters and the prepared statement

		return ['query' => $query, 'params' => $params, 'statement' => $statement];
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
	 * @param   string   $query   SQL query
	 * @param   array    $params  (optional) Query parameters
	 * @return  boolean
	 */

	public function query($query, array $params = [])
	{
		return $this->execute($this->prepare($query, $params));
	}

	/**
	 * Executes the query and return number of affected rows.
	 * 
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  (optional) Query parameters
	 * @return  int
	 */

	public function queryAndCount($query, array $params = [])
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		return $prepared['statement']->rowCount();
	}

	/**
	 * Returns an array containing all of the result set rows.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  (optional) Query parameters
	 * @return  array
	 */

	public function all($query, array $params = [])
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

	public function first($query, array $params = [])
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

	public function column($query, array $params = [])
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		return $prepared['statement']->fetchColumn();
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @access  public
	 * @return  \mako\database\query\Query
	 */

	public function builder()
	{
		return new Query($this);
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