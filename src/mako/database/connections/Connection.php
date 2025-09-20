<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

use Closure;
use Generator;
use mako\database\exceptions\DatabaseException;
use mako\database\exceptions\NotFoundException;
use mako\database\query\compilers\Compiler;
use mako\database\query\helpers\HelperInterface;
use mako\database\query\Query;
use mako\database\types\TypeInterface;
use PDO;
use PDOException;
use PDOStatement;
use SensitiveParameter;
use Stringable;
use Throwable;
use UnitEnum;

use function array_shift;
use function array_splice;
use function count;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_null;
use function is_object;
use function is_string;
use function microtime;
use function preg_replace;
use function preg_replace_callback;
use function sprintf;
use function str_contains;
use function str_repeat;
use function trim;

/**
 * Database connection.
 */
class Connection
{
	/**
	 * Connection DSN.
	 */
	protected string $dsn;

	/**
	 * Database username.
	 */
	protected ?string $username;

	/**
	 * Database password.
	 */
	protected ?string $password;

	/**
	 * Enable the query log?
	 */
	protected bool $enableLog;

	/**
	 * Should we reconnect?
	 */
	protected bool $reconnect;

	/**
	 * Should we use a persistent connection?
	 */
	protected bool $usePersistentConnection;

	/**
	 * PDO options.
	 */
	protected array $options;

	/**
	 * Queries that should be executed upon connecting.
	 */
	protected array $onConnectQueries;

	/**
	 * PDO object.
	 */
	protected ?PDO $pdo;

	/**
	 * Transaction nesting level.
	 */
	protected int $transactionNestingLevel = 0;

	/**
	 * Query log.
	 */
	protected array $log = [];

	/**
	 * Does the connection support transactional DDL?
	 */
	protected bool $supportsTransactionalDDL = false;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $name,
		protected string $queryCompiler,
		protected string $queryBuilderHelper,
		#[SensitiveParameter] array $config
	) {
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
	 * Reset the log and transaction nesting level and create a new PDO instance when cloning the connection.
	 */
	public function __clone()
	{
		$this->name = "{$this->name}_clone";

		$this->log = [];

		$this->transactionNestingLevel = 0;

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
	 */
	public function supportsTransactionalDDL(): bool
	{
		return $this->supportsTransactionalDDL;
	}

	/**
	 * Returns the connection name.
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Returns a query builder helper instance.
	 */
	public function getQueryBuilderHelper(): HelperInterface
	{
		static $queryBuilderHelper = [];

		return $queryBuilderHelper[static::class] ?? ($queryBuilderHelper[static::class] = new $this->queryBuilderHelper);
	}

	/**
	 * Returns a query compiler instance.
	 */
	public function getQueryCompiler(Query $query): Compiler
	{
		return new ($this->queryCompiler)($query);
	}

	/**
	 * Returns the PDO instance or NULL if the connection has been closed.
	 */
	public function getPDO(): ?PDO
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
	 */
	protected function getConnectionOptions(): array
	{
		return $this->options + [
			PDO::ATTR_PERSISTENT         => $this->usePersistentConnection,
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::ATTR_STRINGIFY_FETCHES  => false,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];
	}

	/**
	 * Creates a PDO instance.
	 */
	protected function connect(): PDO
	{
		// Connect to the database

		try {
			$pdo = PDO::connect($this->dsn, $this->username, $this->password, $this->getConnectionOptions());
		}
		catch (PDOException $e) {
			throw new DatabaseException(sprintf('Failed to connect to the [ %s ] database. %s', $this->name, $e->getMessage()), previous: $e);
		}

		// Run queries

		foreach ($this->onConnectQueries as $query) {
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
	 */
	public function isAlive(): bool
	{
		try {
			$this->pdo->query('SELECT 1');
		}
		catch (PDOException $e) {
			return false;
		}

		return true;
	}

	/**
	 * Prepares query for logging.
	 */
	protected function prepareQueryForLog(string $query, array $params): string
	{
		return preg_replace_callback('/\?/', function () use (&$params) {
			$param = array_shift($params);

			if (is_int($param) || is_float($param)) {
				return $param;
			}
			elseif (is_bool(($param))) {
				return $param ? 'TRUE' : 'FALSE';
			}
			elseif (is_null($param)) {
				return 'NULL';
			}
			elseif (is_object($param)) {
				return $param::class;
			}
			else {
				return $this->pdo->quote($param);
			}
		}, $query);
	}

	/**
	 * Adds a query to the query log.
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
	 */
	public function getLog(): array
	{
		return $this->log;
	}

	/**
	 * Prepare query and params.
	 */
	protected function prepareQueryAndParams(string $query, array $params): array
	{
		// Replace IN clause placeholder with escaped values

		replace:

		if (str_contains($query, '([?])')) {
			foreach ($params as $key => $value) {
				if (is_array($value)) {
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
	 */
	protected function isConnectionLostAndShouldItBeReestablished(): bool
	{
		return $this->reconnect === true && $this->inTransaction() === false && $this->isAlive() === false;
	}

	/**
	 * Binds parameter to the prepared statement.
	 */
	protected function bindParameter(PDOStatement $statement, int $key, mixed $value): void
	{
		if (is_string($value)) {
			$type = PDO::PARAM_STR;
		}
		elseif (is_int($value)) {
			$type = PDO::PARAM_INT;
		}
		elseif (is_bool($value)) {
			$type = PDO::PARAM_BOOL;
		}
		elseif (is_null($value)) {
			$type = PDO::PARAM_NULL;
		}
		elseif (is_object($value)) {
			if ($value instanceof UnitEnum) {
				$value = $value->value ?? $value->name;
				$type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
			}
			elseif ($value instanceof TypeInterface) {
				$type = $value->getType();
				$value = $value->getValue();
			}
			elseif ($value instanceof Stringable) {
				$type = PDO::PARAM_STR;
				$value = (string) $value;
			}
			else {
				throw new DatabaseException(sprintf('Unable to bind object of type [ %s ] to the prepared statement.', $value::class));
			}
		}
		else {
			$type = PDO::PARAM_STR;
		}

		$statement->bindValue($key + 1, $value, $type);
	}

	/**
	 * Prepares and executes a query.
	 */
	protected function prepareAndExecute(string $query, array $params, ?bool &$success = null): PDOStatement
	{
		// Prepare query and parameters

		[$query, $params] = $this->prepareQueryAndParams($query, $params);

		// Create prepared statement

		try {
			prepare:

			$statement = $this->pdo->prepare($query);
		}
		catch (PDOException $e) {
			if ($this->isConnectionLostAndShouldItBeReestablished()) {
				$this->reconnect();

				goto prepare;
			}

			throw new DatabaseException("{$e->getMessage()} [ {$this->prepareQueryForLog($query, $params)} ].", (int) $e->getCode(), $e);
		}

		// Bind parameters

		foreach ($params as $key => $value) {
			$this->bindParameter($statement, $key, $value);
		}

		// Execute the query and return the statement

		if ($this->enableLog) {
			$start = microtime(true);
		}

		try {
			execute:

			$success = $statement->execute();
		}
		catch (PDOException $e) {
			if ($this->isConnectionLostAndShouldItBeReestablished()) {
				$this->reconnect();

				goto execute;
			}

			throw new DatabaseException("{$e->getMessage()} [ {$this->prepareQueryForLog($query, $params)} ].", (int) $e->getCode(), $e);
		}

		if ($this->enableLog) {
			$this->log($query, $params, $start);
		}

		return $statement;
	}

	/**
	 * Executes the query and returns TRUE on success or FALSE on failure.
	 */
	public function query(string $query, array $params = []): bool
	{
		$this->prepareAndExecute($query, $params, $success);

		return $success;
	}

	/**
	 * Executes the query and return number of affected rows.
	 */
	public function queryAndCount(string $query, array $params = []): int
	{
		return $this->prepareAndExecute($query, $params)->rowCount();
	}

	/**
	 * Returns the first row of the result set or NULL if nothing is found.
	 */
	public function first(string $query, array $params = [], mixed ...$fetchMode): mixed
	{
		$statement = $this->prepareAndExecute($query, $params);

		if (!empty($fetchMode)) {
			$statement->setFetchMode(...$fetchMode);
		}

		return $statement->fetch() ?: null;
	}

	/**
	 * Returns the first row of the result set or throw an exception if nothing is found.
	 */
	public function firstOrThrow(string $query, array $params = [], string $exception = NotFoundException::class, mixed ...$fetchMode): mixed
	{
		if (($row = $this->first($query, $params, ...$fetchMode)) === null) {
			throw new $exception;
		}

		return $row;
	}

	/**
	 * Returns an array containing all of the result set rows.
	 */
	public function all(string $query, array $params = [], mixed ...$fetchMode): array
	{
		return $this->prepareAndExecute($query, $params)->fetchAll(...$fetchMode);
	}

	/**
	 * Returns the value of the first column of the first row of the result set or NULL if nothing is found.
	 */
	public function column(string $query, array $params = []): mixed
	{
		return $this->prepareAndExecute($query, $params)->fetch(PDO::FETCH_NUM)[0] ?? null;
	}

	/**
	 * Returns an array containing the values of the indicated 0-indexed column.
	 */
	public function columns(string $query, array $params = []): array
	{
		return $this->all($query, $params, PDO::FETCH_COLUMN);
	}

	/**
	 * Returns an array where the first column is used as keys and the second as values.
	 */
	public function pairs(string $query, array $params = []): array
	{
		return $this->all($query, $params, PDO::FETCH_KEY_PAIR);
	}

	/**
	 * Returns a generator that lets you iterate over the results.
	 */
	public function yield(string $query, array $params = [], mixed ...$fetchMode): Generator
	{
		$statement = $this->prepareAndExecute($query, $params);

		if (!empty($fetchMode)) {
			$statement->setFetchMode(...$fetchMode);
		}

		try {
			while ($row = $statement->fetch()) {
				yield $row;
			}
		}
		finally {
			$statement->closeCursor();
		}
	}

	/**
	 * Returns a resource that allows you to treat a column value as a byte stream.
	 *
	 * @return resource|null
	 */
	public function blob(string $query, array $params = [], int|string $column = 1)
	{
		$statement = $this->prepareAndExecute($query, $params);

		$statement->bindColumn($column, $blob, PDO::PARAM_LOB);

		$statement->fetch(PDO::FETCH_BOUND);

		/** @var resource|null $blob */
		return $blob;
	}

	/**
	 * Returns a query builder instance.
	 */
	public function getQuery(): Query
	{
		return new Query($this);
	}

	/**
	 * Creates a new savepoint.
	 */
	protected function createSavepoint(): bool
	{
		return $this->pdo->exec("SAVEPOINT transactionNestingLevel{$this->transactionNestingLevel}") !== false;
	}

	/**
	 * Rolls back to the previously created savepoint.
	 */
	protected function rollBackSavepoint(): bool
	{
		return $this->pdo->exec("ROLLBACK TO SAVEPOINT transactionNestingLevel{$this->transactionNestingLevel}") !== false;
	}

	/**
	 * Begin a transaction.
	 */
	public function beginTransaction(): bool
	{
		if ($this->transactionNestingLevel++ === 0) {
			return $this->pdo->beginTransaction();
		}

		return $this->createSavepoint();
	}

	/**
	 * Commits a transaction.
	 */
	public function commitTransaction(): bool
	{
		if ($this->transactionNestingLevel > 0 && --$this->transactionNestingLevel === 0) {
			return $this->pdo->commit();
		}

		return false;
	}

	/**
	 * Roll back a transaction.
	 */
	public function rollBackTransaction(): bool
	{
		if ($this->transactionNestingLevel > 0) {
			if ($this->transactionNestingLevel > 1) {
				$success = $this->rollBackSavepoint();
			}
			else {
				$success =  $this->pdo->rollBack();
			}

			$this->transactionNestingLevel--;

			return $success;
		}

		return false;
	}

	/**
	 * Returns the transaction nesting level.
	 */
	public function getTransactionNestingLevel(): int
	{
		return $this->transactionNestingLevel;
	}

	/**
	 * Returns TRUE if we're in a transaction and FALSE if not.
	 */
	public function inTransaction(): bool
	{
		return $this->pdo->inTransaction();
	}

	/**
	 * Executes queries and rolls back the transaction if any of them fail.
	 */
	public function transaction(Closure $queries): mixed
	{
		try {
			$this->beginTransaction();

			$returnValue = $queries($this);

			$this->commitTransaction();
		}
		catch (Throwable $e) {
			$this->rollBackTransaction();

			throw new DatabaseException('Exception caught. The transaction has been rolled back.', previous: $e);
		}

		return $returnValue;
	}
}
