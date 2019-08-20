<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

use Closure;
use Generator;
use mako\database\query\compilers\Compiler;
use mako\database\query\helpers\HelperInterface;
use mako\database\query\Query;
use mako\database\types\TypeInterface;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

use function array_shift;
use function array_splice;
use function count;
use function get_class;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_null;
use function is_object;
use function is_resource;
use function is_string;
use function microtime;
use function preg_replace;
use function preg_replace_callback;
use function str_repeat;
use function strpos;
use function trim;
use function vsprintf;

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
	 * PDO options.
	 *
	 * @var array
	 */
	protected $options;

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
	 * Does the connection support transactional DDL?
	 *
	 * @var bool
	 */
	protected $supportsTransactionalDDL = false;

	/**
	 * Constructor.
	 *
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

		$this->options = $config['options'] ?? [];

		$this->onConnectQueries = $config['queries'] ?? [];

		// Connect to the database

		$this->pdo = $this->connect();
	}

	/**
	 * Closes the database connection.
	 */
	public function close(): void
	{
		$this->pdo = null;
	}

	/**
	 * Does the connection support transactional DDL?
	 *
	 * @return bool
	 */
	public function supportsTransactionalDDL(): bool
	{
		return $this->supportsTransactionalDDL;
	}

	/**
	 * Returns the connection name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Returns a query builder helper instance.
	 *
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
	 * @return \PDO
	 */
	public function getPDO(): PDO
	{
		return $this->pdo;
	}

	/**
	 * Enables the query log.
	 */
	public function enableLog(): void
	{
		$this->enableLog = true;
	}

	/**
	 * Disables the query log.
	 */
	public function disableLog(): void
	{
		$this->enableLog = false;
	}

	/**
	 * Returns the connection options.
	 *
	 * @return array
	 */
	protected function getConnectionOptions(): array
	{
		return $this->options +
		[
			PDO::ATTR_PERSISTENT         => $this->usePersistentConnection,
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::ATTR_STRINGIFY_FETCHES  => false,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];
	}

	/**
	 * Creates a PDO instance.
	 *
	 * @return \PDO
	 */
	protected function connect(): PDO
	{
		// Connect to the database

		try
		{
			$pdo = new PDO($this->dsn, $this->username, $this->password, $this->getConnectionOptions());
		}
		catch(PDOException $e)
		{
			throw new RuntimeException(vsprintf('Failed to connect to the [ %s ] database. %s', [$this->name, $e->getMessage()]));
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
	 */
	public function reconnect(): void
	{
		$this->pdo = $this->connect();
	}

	/**
	 * Checks if the connection is alive.
	 *
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
	 * @param  string $query  SQL query
	 * @param  array  $params Query paramaters
	 * @return string
	 */
	protected function prepareQueryForLog(string $query, array $params): string
	{
		return preg_replace_callback('/\?/', function() use (&$params)
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
			elseif(is_null($param))
			{
				return 'NULL';
			}
			elseif(is_resource($param))
			{
				return 'resource';
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
	 * @param string $query  SQL query
	 * @param array  $params Query parameters
	 * @param float  $start  Start time in microseconds
	 */
	protected function log(string $query, array $params, float $start): void
	{
		$time = microtime(true) - $start;

		$query = $this->prepareQueryForLog($query, $params);

		$this->log[] = ['query' => $query, 'time' => $time];
	}

	/**
	 * Clears the query log.
	 */
	public function clearLog(): void
	{
		$this->log = [];
	}

	/**
	 * Returns the query log for the connection.
	 *
	 * @return array
	 */
	public function getLog(): array
	{
		return $this->log;
	}

	/**
	 * Prepare query and params.
	 *
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
	 * @return bool
	 */
	protected function isConnectionLostAndShouldItBeReestablished(): bool
	{
		return ($this->reconnect === true && $this->inTransaction() === false && $this->isAlive() === false);
	}

	/**
	 * Binds parameter to the prepared statement.
	 *
	 * @param \PDOStatement $statement PDO statement
	 * @param int           $key       Parameter key
	 * @param mixed         $value     Parameter value
	 */
	protected function bindParameter(PDOStatement $statement, int $key, $value): void
	{
		if(is_string($value))
		{
			$type = PDO::PARAM_STR;
		}
		elseif(is_int($value))
		{
			$type = PDO::PARAM_INT;
		}
		elseif(is_bool($value))
		{
			$type = PDO::PARAM_BOOL;
		}
		elseif(is_null($value))
		{
			$type = PDO::PARAM_NULL;
		}
		elseif(is_object($value) && $value instanceof TypeInterface)
		{
			$value = $value->getValue();

			$type = $value->getType();
		}
		elseif(is_resource($value))
		{
			$type = PDO::PARAM_LOB;
		}
		else
		{
			$type = PDO::PARAM_STR;
		}

		$statement->bindValue($key + 1, $value, $type);
	}

	/**
	 * Prepares and executes a query.
	 *
	 * @param  string        $query    SQL query
	 * @param  array         $params   Query parameters
	 * @param  bool          &$success Was the query executed successfully?
	 * @return \PDOStatement
	 */
	protected function prepareAndExecute(string $query, array $params, bool &$success = null): PDOStatement
	{
		// Prepare query and parameters

		[$query, $params] = $this->prepareQueryAndParams($query, $params);

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

			throw new PDOException("{$e->getMessage()} [ {$this->prepareQueryForLog($query, $params)} ].", (int) $e->getCode(), $e->getPrevious());
		}

		// Bind parameters

		foreach($params as $key => $value)
		{
			$this->bindParameter($statement, $key, $value);
		}

		// Execute the query and return the statement

		if($this->enableLog)
		{
			$start = microtime(true);
		}

		$success = $statement->execute();

		if($this->enableLog)
		{
			$this->log($query, $params, $start);
		}

		return $statement;
	}

	/**
	 * Executes the query and returns TRUE on success or FALSE on failure.
	 *
	 * @param  string $query  SQL query
	 * @param  array  $params Query parameters
	 * @return bool
	 */
	public function query(string $query, array $params = []): bool
	{
		$this->prepareAndExecute($query, $params, $success);

		return $success;
	}

	/**
	 * Executes the query and return number of affected rows.
	 *
	 * @param  string $query  SQL query
	 * @param  array  $params Query parameters
	 * @return int
	 */
	public function queryAndCount(string $query, array $params = []): int
	{
		return $this->prepareAndExecute($query, $params)->rowCount();
	}

	/**
	 * Returns the first row of the result set.
	 *
	 * @param  string     $query        SQL query
	 * @param  array      $params       Query params
	 * @param  mixed      ...$fetchMode Fetch mode
	 * @return mixed|bool
	 */
	public function first(string $query, array $params = [], ...$fetchMode)
	{
		$statement = $this->prepareAndExecute($query, $params);

		if(!empty($fetchMode))
		{
			$statement->setFetchMode(...$fetchMode);
		}

		return $statement->fetch();
	}

	/**
	 * Returns an array containing all of the result set rows.
	 *
	 * @param  string $query        SQL query
	 * @param  array  $params       Query parameters
	 * @param  mixed  ...$fetchMode Fetch mode
	 * @return array
	 */
	public function all(string $query, array $params = [], ...$fetchMode): array
	{
		return $this->prepareAndExecute($query, $params)->fetchAll(...$fetchMode);
	}

	/**
	 * Returns the value of the first column of the first row of the result set.
	 *
	 * @param  string     $query  SQL query
	 * @param  array      $params Query parameters
	 * @return mixed|bool
	 */
	public function column(string $query, array $params = [])
	{
		return $this->prepareAndExecute($query, $params)->fetchColumn();
	}

	/**
	 * Returns an array containing the values of the indicated 0-indexed column.
	 *
	 * @param  string $query  SQL query
	 * @param  array  $params Query parameters
	 * @return array
	 */
	public function columns(string $query, array $params = []): array
	{
		return $this->all($query, $params, PDO::FETCH_COLUMN);
	}

	/**
	 * Returns an array where the first column is used as keys and the second as values.
	 *
	 * @param  string $query  SQL query
	 * @param  array  $params Query parameters
	 * @return array
	 */
	public function pairs(string $query, array $params = []): array
	{
		return $this->all($query, $params, PDO::FETCH_KEY_PAIR);
	}

	/**
	 * Returns a generator that lets you iterate over the results.
	 *
	 * @param  string     $query        SQL query
	 * @param  array      $params       Query params
	 * @param  mixed      ...$fetchMode Fetch mode
	 * @return \Generator
	 */
	public function yield(string $query, array $params = [], ...$fetchMode): Generator
	{
		$statement = $this->prepareAndExecute($query, $params);

		if(!empty($fetchMode))
		{
			$statement->setFetchMode(...$fetchMode);
		}

		try
		{
			while($row = $statement->fetch())
			{
				yield $row;
			}
		}
		finally
		{
			$statement->closeCursor();
		}
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @return \mako\database\query\Query
	 */
	public function builder(): Query
	{
		return new Query($this);
	}

	/**
	 * Returns a query builder instance where we have already chosen the table we want to query.
	 *
	 * @param  null|string|array|\Closure|\mako\database\query\Subquery|\mako\database\query\Raw $table Database table or subquery
	 * @return \mako\database\query\Query
	 */
	public function table($table): Query
	{
		return $this->builder()->table($table);
	}

	/**
	 * Creates a new savepoint.
	 *
	 * @return bool
	 */
	protected function createSavepoint(): bool
	{
		return $this->pdo->exec("SAVEPOINT transactionNestingLevel{$this->transactionNestingLevel}") !== false;
	}

	/**
	 * Rolls back to the previously created savepoint.
	 *
	 * @return bool
	 */
	protected function rollBackSavepoint(): bool
	{
		return $this->pdo->exec("ROLLBACK TO SAVEPOINT transactionNestingLevel{$this->transactionNestingLevel}") !== false;
	}

	/**
	 * Begin a transaction.
	 *
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
	 * @return int
	 */
	public function getTransactionNestingLevel(): int
	{
		return $this->transactionNestingLevel;
	}

	/**
	 * Returns TRUE if we're in a transaction and FALSE if not.
	 *
	 * @return bool
	 */
	public function inTransaction(): bool
	{
		return $this->pdo->inTransaction();
	}

	/**
	 * Executes queries and rolls back the transaction if any of them fail.
	 *
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
