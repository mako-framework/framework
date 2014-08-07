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
	 * Connection DSN.
	 * 
	 * @var string
	 */

	protected $dsn;

	/**
	 * Database username.
	 * 
	 * @var string
	 */

	protected $username;

	/**
	 * Database password.
	 * 
	 * @var string
	 */

	protected $password;

	/**
	 * Enable the query log?
	 *
	 * @var boolean
	 */

	protected $enableLog;

	/**
	 * Should we reconnect?
	 * 
	 * @var boolean
	 */

	protected $reconnect;

	/**
	 * Should we use a persistent connection?
	 * 
	 * @var boolean
	 */

	protected $usePersistentConnection;

	/**
	 * Queries that should be executed upon connecting.
	 * 
	 * @var array
	 */

	protected $onConnectQueries;

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
	 * Transaction nesting level.
	 * 
	 * @var int
	 */

	protected $transactionNestingLevel = 0;

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

		// Configure the connection

		$this->dsn = $config['dsn'];

		$this->username = isset($config['username']) ? $config['username'] : null;

		$this->password = isset($config['password']) ? $config['password'] : null;

		$this->enableLog = isset($config['log_queries']) ? $config['log_queries'] : false;

		$this->reconnect = isset($config['reconnect']) ? $config['reconnect'] : false;

		$this->usePersistentConnection = isset($config['persistent']) ? $config['persistent'] : false;

		$this->onConnectQueries = isset($config['queries']) ? $config['queries'] : [];

		// Connect to the database

		$this->pdo = $this->connect();

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
	 * Creates a PDO instance.
	 * 
	 * @access  protected
	 * @return  \PDO
	 */

	protected function connect()
	{
		// Connect to the database

		$options = 
		[
			PDO::ATTR_PERSISTENT         => $this->usePersistentConnection,
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::ATTR_STRINGIFY_FETCHES  => false,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];

		try
		{
			$pdo = new PDO($this->dsn, $this->username, $this->password, $options);
		}
		catch(PDOException $e)
		{
			throw new RuntimeException(vsprintf("%s(): Failed to connect to the [ %s ] database. %s", [__METHOD__, $this->name, $e->getMessage()]));
		}

		// Run queries

		foreach($this->onConnectQueries as $query)
		{
			$pdo->exec($query);
		}

		// Return PDO instance

		return $pdo;
	}

	/**
	 * Creates a new PDO instance.
	 * 
	 * @access  public
	 */

	public function reconnect()
	{
		$this->pdo = $this->connect();
	}

	/**
	 * Pings the database server and returns TRUE if the 
	 * connection is alive and FALSE if not.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function ping()
	{
		switch($this->driver)
		{
			case 'db2':
			case 'ibm':
			case 'odbc':
				$query = 'SELECT 1 FROM SYSIBM.SYSDUMMY1';
				break;
			case 'oci':
			case 'oracle':
				$query = 'SELECT 1 FROM DUAL';
				break;
			default:
				$query = 'SELECT 1';
		}

		try
		{
			$this->pdo->query($query);
		}
		catch(PDOException $e)
		{
			return false;
		}

		return true;
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
	 * Prepare query and params.
	 * 
	 * @access  protected
	 * @param   string     $query   SQL Query
	 * @param   array      $params  Query parameters
	 * @return  array
	 */

	protected function prepareQueryAndParams($query, array $params)
	{
		replace:

		if(strpos($query, '([?])') !== false)
		{
			// Replace IN clause placeholder with escaped values

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

		return [$query, $params];
	}

	/**
	 * Should we try to reestablish the connection?
	 * 
	 * @access  protected
	 * @return  boolean
	 */

	protected function isConnectionLostAndShouldItBeReestablished()
	{
		return ($this->reconnect === true && $this->inTransaction() === false && $this->ping() === false);
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
		// Prepare query and parameters

		list($query, $params) = $this->prepareQueryAndParams($query, $params);

		// Create a prepared statement

		try
		{
			prepare:

			$statement = $this->pdo->prepare($query);
		}
		catch(PDOException $e)
		{
			if($this->isConnectionLostAndShouldItBeReestablished())
			{
				$this->reconnect();

				goto prepare;
			}

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
	 * Begin a transaction.
	 * 
	 * @access  public
	 */

	public function beginTransaction()
	{
		$this->transactionNestingLevel++;

		if($this->transactionNestingLevel === 1)
		{
			$this->pdo->beginTransaction();
		}
	}

	/**
	 * Commits a transaction.
	 * 
	 * @access  public
	 */

	public function commitTransaction()
	{
		if($this->transactionNestingLevel > 0)
		{
			$this->transactionNestingLevel--;
		}

		if($this->transactionNestingLevel === 0)
		{
			$this->pdo->commit();
		}
	}

	/**
	 * Roll back a transaction.
	 * 
	 * @access  public
	 */

	public function rollBackTransaction()
	{
		if($this->transactionNestingLevel > 0)
		{
			$this->transactionNestingLevel--;
		}

		if($this->transactionNestingLevel === 0)
		{
			$this->pdo->rollBack();
		}
	}

	/**
	 * Returns the transaction nesting level.
	 * 
	 * @access  public
	 * @return  int
	 */

	public function getTransactionNestingLevel()
	{
		return $this->transactionNestingLevel;
	}

	/**
	 * Returns TRUE if we're in a transaction and FALSE if not.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function inTransaction()
	{
		return $this->pdo->inTransaction();
	}

	/**
	 * Executes queries and rolls back the transaction if any of them fail.
	 *
	 * @access  public
	 * @param   \Closure  $queries  Queries
	 */

	public function transaction(Closure $queries)
	{
		try
		{
			$this->beginTransaction();

			$queries($this);

			$this->commitTransaction();
		}
		catch(PDOException $e)
		{
			$this->rollBackTransaction();

			throw $e;
		}
	}
}