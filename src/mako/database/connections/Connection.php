<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\connections;

use PDO;
use Closure;
use PDOException;
use RuntimeException;

use mako\database\query\Query;
use mako\database\types\TypeInterface;

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
	 * Query compiler.
	 *
	 * @var string.
	 */
	protected $queryCompiler;

	/**
	 * Query builder helper.
	 *
	 * @var string
	 */
	protected $queryBuilderHelper;

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
	 * @param   string  $name                Connection name
	 * @param   string  $queryCompiler       Query compiler
	 * @param   string  $queryBuilderHelper  Query builder helper
	 * @param   array   $config              Connection configuration
	 */
	public function __construct($name, $queryCompiler, $queryBuilderHelper, array $config)
	{
		$this->name = $name;

		// Set the query compiler and query builder helper

		$this->queryCompiler = $queryCompiler;

		$this->queryBuilderHelper = $queryBuilderHelper;

		// Configure the connection

		$this->dsn = $config['dsn'];

		$this->username = $config['username'] ?? null;

		$this->password = $config['password'] ?? null;

		$this->enableLog = $config['log_queries'] ?? false;

		$this->reconnect = $config['reconnect'] ?? false;

		$this->usePersistentConnection = $config['persistent'] ?? false;

		$this->onConnectQueries = $config['queries'] ?? [];

		// Connect to the database

		$this->pdo = $this->connect();
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
	 * Returns a query builder helper instance.
	 *
	 * @access  public
	 * @return  \mako\database\query\helpers\HelperInterface
	 */
	public function getQueryBuilderHelper()
	{
		static $queryBuilderHelper;

		return $queryBuilderHelper ?? ($queryBuilderHelper = new $this->queryBuilderHelper);
	}

	/**
	 * Returns a query compiler instance.
	 *
	 * @access  public
	 * @param   \mako\database\query\Query               $query  Query
	 * @return  \mako\database\query\compilers\Compiler
	 */
	public function getQueryCompiler(Query $query)
	{
		$compiler = $this->queryCompiler;

		return new $compiler($query);
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
	 * Checks if the connection is alive.
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function isAlive()
	{
		try
		{
			$this->pdo->query('SELECT 1');
		}
		catch(PDOException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Prepares query for logging.
	 *
	 * @access  protected
	 * @param   string     $query   SQL query
	 * @param   array      $params  Query paramaters
	 * @return  string
	 */
	protected function prepareQueryForLog($query, array $params)
	{
		return preg_replace_callback('/\?/', function($matches) use (&$params)
		{
			$param = array_shift($params);

			if(is_int($param) || is_float($param))
			{
				return $param;
			}
			elseif(is_bool(($param)))
			{
				return $param ? 'TRUE' : 'FALSE';
			}
			elseif(is_object($param))
			{
				return get_class($param);
			}
			else
			{
				return $this->pdo->quote($param);
			}
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

		$query = $this->prepareQueryForLog($query, $params);

		$this->log[] = ['query' => $query, 'time' => $time];
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

		// Return query and parameters

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
		return ($this->reconnect === true && $this->inTransaction() === false && $this->isAlive() === false);
	}

	/**
	 * Binds parameter to the prepared statement.
	 *
	 * @access  protected
	 * @param   \PDOStatement  $statement  PDO statement
	 * @param   int            $key        Parameter key
	 * @param   mixed          $value      Parameter value
	 */
	protected function bindParameter($statement, $key, $value)
	{
		if($value instanceof TypeInterface)
		{
			$value = $value->getValue();

			$type = $value->getType();
		}
		else
		{
			switch(gettype($value))
			{
				case 'boolean':
					$type = PDO::PARAM_BOOL;
					break;
				case 'integer':
					$type = PDO::PARAM_INT;
					break;
				case 'NULL':
					$type = PDO::PARAM_NULL;
					break;
				case 'resource':
					$type = PDO::PARAM_LOB;
					break;
				default:
					$type = PDO::PARAM_STR;
			}
		}

		$statement->bindValue($key + 1, $value, $type);
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

		// Create prepared statement

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

			throw new PDOException($e->getMessage() . ' [ ' . $this->prepareQueryForLog($query, $params) . ' ] ', (int) $e->getCode(), $e->getPrevious());
		}

		// Bind parameters

		foreach($params as $key => $value)
		{
			$this->bindParameter($statement, $key, $value);
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

		$result = $prepared['statement']->execute();

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
	 * @param   array    $params  Query parameters
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
	 * @param   array   $params  Query parameters
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
	 * @param   string    $query         SQL query
	 * @param   array     $params        Query parameters
	 * @param   mixed     ...$fetchMode  Fetch mode
	 * @return  array
	 */
	public function all($query, array $params = [], ...$fetchMode)
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		return $prepared['statement']->fetchAll(...$fetchMode);
	}

	/**
	 * Returns the first row of the result set.
	 *
	 * @access  public
	 * @param   string    $query         SQL query
	 * @param   array     $params        Query params
	 * @param   mixed     ...$fetchMode  Fetch mode
	 * @return  mixed
	 */
	public function first($query, array $params = [], ...$fetchMode)
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		return $prepared['statement']->fetch(...$fetchMode);
	}

	/**
	 * Returns the value of the first column of the first row of the result set.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  Query parameters
	 * @return  mixed
	 */
	public function column($query, array $params = [])
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		return $prepared['statement']->fetchColumn();
	}

	/**
	 * Executes a SELECT query and returns an array containing the values of the indicated 0-indexed column.
	 *
	 * @access  public
	 * @param   string  $query   SQL query
	 * @param   array   $params  Query parameters
	 * @return  array
	 */
	public function columns($query, array $params = [])
	{
		return $this->all($query, $params, PDO::FETCH_COLUMN);
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @access  public
	 * @return  \mako\database\query\Query
	 */
	public function builder()
	{
		return new Query($this, new $this->queryBuilderHelper, $this->queryCompiler);
	}

	/**
	 * Returns a query builder instance where we have already chosen the table we want to query.
	 *
	 * @access  public
	 * @param   string|\Closure|\mako\database\query\Subquery|\mako\database\query\Raw  $table  Database table or subquery
	 * @return  \mako\database\query\Query
	 */
	public function table($table)
	{
		return $this->builder()->table($table);
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