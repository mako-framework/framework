<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

use Closure;
use Generator;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

use mako\database\query\Query;
use mako\database\query\compilers\Compiler;
use mako\database\query\helpers\HelperInterface;
use mako\database\types\TypeInterface;

/**
 * Database connection.
 *
 * @author Frederic G. Østby
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
	 * @var bool
	 */
	protected $enableLog;

	/**
	 * Should we reconnect?
	 *
	 * @var bool
	 */
	protected $reconnect;

	/**
	 * Should we use a persistent connection?
	 *
	 * @var bool
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
	 * @access public
	 * @param string $name               Connection name
	 * @param string $queryCompiler      Query compiler
	 * @param string $queryBuilderHelper Query builder helper
	 * @param array  $config             Connection configuration
	 */
	public function __construct(string $name, string $queryCompiler, string $queryBuilderHelper, array $config)
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
	 * @access public
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Returns a query builder helper instance.
	 *
	 * @access public
	 * @return \mako\database\query\helpers\HelperInterface
	 */
	public function getQueryBuilderHelper(): HelperInterface
	{
		static $queryBuilderHelper;

		return $queryBuilderHelper ?? ($queryBuilderHelper = new $this->queryBuilderHelper);
	}

	/**
	 * Returns a query compiler instance.
	 *
	 * @access public
	 * @param  \mako\database\query\Query              $query Query
	 * @return \mako\database\query\compilers\Compiler
	 */
	public function getQueryCompiler(Query $query): Compiler
	{
		$compiler = $this->queryCompiler;

		return new $compiler($query);
	}

	/**
	 * Returns the PDO instance.
	 *
	 * @access public
	 * @return \PDO
	 */
	public function getPDO(): PDO
	{
		return $this->pdo;
	}

	/**
	 * Enables the query log.
	 *
	 * @access public
	 */
	public function enableLog()
	{
		$this->enableLog = true;
	}

	/**
	 * Disables the query log.
	 *
	 * @access public
	 */
	public function disableLog()
	{
		$this->enableLog = false;
	}

	/**
	 * Creates a PDO instance.
	 *
	 * @access protected
	 * @return \PDO
	 */
	protected function connect(): PDO
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
	 * @access public
	 */
	public function reconnect()
	{
		$this->pdo = $this->connect();
	}

	/**
	 * Checks if the connection is alive.
	 *
	 * @access public
	 * @return bool
	 */
	public function isAlive(): bool
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
	 * @access protected
	 * @param  string $query  SQL query
	 * @param  array  $params Query paramaters
	 * @return string
	 */
	protected function prepareQueryForLog(string $query, array $params): string
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
	 * @access protected
	 * @param string $query  SQL query
	 * @param array  $params Query parameters
	 * @param float  $start  Start time in microseconds
	 */
	protected function log(string $query, array $params, float $start)
	{
		$time = microtime(true) - $start;

		$query = $this->prepareQueryForLog($query, $params);

		$this->log[] = ['query' => $query, 'time' => $time];
	}

	/**
	 * Clears the query log.
	 *
	 * @access public
	 */
	public function clearLog()
	{
		$this->log = [];
	}

	/**
	 * Returns the query log for the connection.
	 *
	 * @access public
	 * @return array
	 */
	public function getLog(): array
	{
		return $this->log;
	}

	/**
	 * Prepare query and params.
	 *
	 * @access protected
	 * @param  string $query  SQL Query
	 * @param  array  $params Query parameters
	 * @return array
	 */
	protected function prepareQueryAndParams(string $query, array $params): array
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
	 * @access protected
	 * @return bool
	 */
	protected function isConnectionLostAndShouldItBeReestablished(): bool
	{
		return ($this->reconnect === true && $this->inTransaction() === false && $this->isAlive() === false);
	}

	/**
	 * Binds parameter to the prepared statement.
	 *
	 * @access protected
	 * @param \PDOStatement $statement PDO statement
	 * @param int           $key       Parameter key
	 * @param mixed         $value     Parameter value
	 */
	protected function bindParameter(PDOStatement $statement, int $key, $value)
	{
		if($value instanceof TypeInterface)
		{
			$value = $value->getValue();

			$type = $value->getType();
		}
		else
		{
			if(is_bool($value))
			{
				$type = PDO::PARAM_BOOL;
			}
			elseif(is_int($value))
			{
				$type = PDO::PARAM_INT;
			}
			elseif(is_null($value))
			{
				$type = PDO::PARAM_NULL;
			}
			elseif(is_resource($value))
			{
				$type = PDO::PARAM_LOB;
			}
			else
			{
				$type = PDO::PARAM_STR;
			}
		}

		$statement->bindValue($key + 1, $value, $type);
	}

	/**
	 * Prepares a query.
	 *
	 * @access protected
	 * @param  string $query  SQL query
	 * @param  array  $params Query parameters
	 * @return array
	 */
	protected function prepare(string $query, array $params): array
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
	 * @access protected
	 * @param  array $prepared Prepared query
	 * @return bool
	 */
	protected function execute(array $prepared): bool
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
	 * @access public
	 * @param  string $query  SQL query
	 * @param  array  $params Query parameters
	 * @return bool
	 */
	public function query(string $query, array $params = []): bool
	{
		return $this->execute($this->prepare($query, $params));
	}

	/**
	 * Executes the query and return number of affected rows.
	 *
	 * @access public
	 * @param  string $query  SQL query
	 * @param  array  $params Query parameters
	 * @return int
	 */
	public function queryAndCount(string $query, array $params = []): int
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		return $prepared['statement']->rowCount();
	}

	/**
	 * Returns the value of the first column of the first row of the result set.
	 *
	 * @access public
	 * @param  string $query  SQL query
	 * @param  array  $params Query parameters
	 * @return mixed
	 */
	public function column(string $query, array $params = [])
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		return $prepared['statement']->fetchColumn();
	}

	/**
	 * Executes a SELECT query and returns an array containing the values of the indicated 0-indexed column.
	 *
	 * @access public
	 * @param  string $query  SQL query
	 * @param  array  $params Query parameters
	 * @return array
	 */
	public function columns(string $query, array $params = []): array
	{
		return $this->all($query, $params, PDO::FETCH_COLUMN);
	}

	/**
	 * Returns the first row of the result set.
	 *
	 * @access public
	 * @param string $query  SQL query
	 * @param array  $params Query params
	 * @param   mixed     ...$fetchMode  Fetch mode
	 * @return mixed
	 */
	public function first(string $query, array $params = [], ...$fetchMode)
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		if(!empty($fetchMode))
		{
			$prepared['statement']->setFetchMode(...$fetchMode);
		}

		return $prepared['statement']->fetch();
	}

	/**
	 * Returns an array containing all of the result set rows.
	 *
	 * @access public
	 * @param string $query  SQL query
	 * @param array  $params Query parameters
	 * @param   mixed     ...$fetchMode  Fetch mode
	 * @return array
	 */
	public function all(string $query, array $params = [], ...$fetchMode): array
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		return $prepared['statement']->fetchAll(...$fetchMode);
	}

	/**
	 * Returns a generator that lets you iterate over the results.
	 *
	 * @access public
	 * @param string $query  SQL query
	 * @param array  $params Query params
	 * @param   mixed       ...$fetchMode  Fetch mode
	 * @return \Generator
	 */
	public function yield(string $query, array $params = [], ...$fetchMode): Generator
	{
		$prepared = $this->prepare($query, $params);

		$this->execute($prepared);

		if(!empty($fetchMode))
		{
			$prepared['statement']->setFetchMode(...$fetchMode);
		}

		try
		{
			while($row = $prepared['statement']->fetch())
			{
				yield $row;
			}
		}
		finally
		{
			$prepared['statement']->closeCursor();
		}
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @access public
	 * @return \mako\database\query\Query
	 */
	public function builder(): Query
	{
		return new Query($this);
	}

	/**
	 * Returns a query builder instance where we have already chosen the table we want to query.
	 *
	 * @access public
	 * @param  string|\Closure|\mako\database\query\Subquery|\mako\database\query\Raw $table Database table or subquery
	 * @return \mako\database\query\Query
	 */
	public function table($table): Query
	{
		return $this->builder()->table($table);
	}

	/**
	 * Creates a new savepoint.
	 *
	 * @access protected
	 * @return bool
	 */
	protected function createSavepoint(): bool
	{
		return $this->pdo->exec('SAVEPOINT transactionNestingLevel' . $this->transactionNestingLevel) !== false;
	}

	/**
	 * Rolls back to the previously created savepoint.
	 *
	 * @access protected
	 * @return bool
	 */
	protected function rollBackSavepoint(): bool
	{
		return $this->pdo->exec('ROLLBACK TO SAVEPOINT transactionNestingLevel' . $this->transactionNestingLevel) !== false;
	}

	/**
	 * Begin a transaction.
	 *
	 * @access public
	 * @return bool
	 */
	public function beginTransaction(): bool
	{
		if($this->transactionNestingLevel++ === 0)
		{
			return $this->pdo->beginTransaction();
		}

		return $this->createSavepoint();
	}

	/**
	 * Commits a transaction.
	 *
	 * @access public
	 * @return bool
	 */
	public function commitTransaction(): bool
	{
		if($this->transactionNestingLevel > 0 && --$this->transactionNestingLevel === 0)
		{
			return $this->pdo->commit();
		}

		return false;
	}

	/**
	 * Roll back a transaction.
	 *
	 * @access public
	 * @return bool
	 */
	public function rollBackTransaction(): bool
	{
		if($this->transactionNestingLevel > 0)
		{
			if($this->transactionNestingLevel > 1)
			{
				$success = $this->rollBackSavepoint();
			}
			else
			{
				$success =  $this->pdo->rollBack();
			}

			$this->transactionNestingLevel--;

			return $success;
		}

		return false;
	}

	/**
	 * Returns the transaction nesting level.
	 *
	 * @access public
	 * @return int
	 */
	public function getTransactionNestingLevel(): int
	{
		return $this->transactionNestingLevel;
	}

	/**
	 * Returns TRUE if we're in a transaction and FALSE if not.
	 *
	 * @access public
	 * @return bool
	 */
	public function inTransaction(): bool
	{
		return $this->pdo->inTransaction();
	}

	/**
	 * Executes queries and rolls back the transaction if any of them fail.
	 *
	 * @access public
	 * @param  \Closure $queries Queries
	 * @return mixed
	 */
	public function transaction(Closure $queries)
	{
		try
		{
			$this->beginTransaction();

			$returnValue = $queries($this);

			$this->commitTransaction();
		}
		catch(PDOException $e)
		{
			$this->rollBackTransaction();

			throw $e;
		}

		return $returnValue;
	}
}
