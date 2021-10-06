<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database;

use mako\common\ConnectionManager as BaseConnectionManager;
use mako\database\connections\Connection;
use mako\database\connections\Firebird as FirebirdConnection;
use mako\database\connections\MySQL as MySQLConnection;
use mako\database\connections\Oracle as OracleConnection;
use mako\database\connections\Postgres as PostgresConnection;
use mako\database\connections\SQLite as SQLiteConnection;
use mako\database\connections\SQLServer as SQLServerConnection;
use mako\database\exceptions\DatabaseException;
use mako\database\query\compilers\Compiler;
use mako\database\query\compilers\Firebird as FirebirdCompiler;
use mako\database\query\compilers\MySQL as MySQLCompiler;
use mako\database\query\compilers\NuoDB as NuoDBCompiler;
use mako\database\query\compilers\Oracle as OracleCompiler;
use mako\database\query\compilers\Postgres as PostgresCompiler;
use mako\database\query\compilers\SQLite as SQLiteCompiler;
use mako\database\query\compilers\SQLServer as SQLServerCompiler;
use mako\database\query\helpers\Helper;
use mako\database\query\helpers\Postgres as PostgresHelper;

use function array_merge;
use function explode;
use function in_array;
use function vsprintf;

/**
 * Database connection manager.
 *
 * @method \mako\database\connections\Connection connection($connection = null)
 * @method \mako\database\connections\Connection getConnection($connection = null)
 */
class ConnectionManager extends BaseConnectionManager
{
	/**
	 * Driver aliases.
	 *
	 * @var array
	 */
	protected $driverAliases =
	[
		'oracle' => ['oci', 'oracle'],
		'sqlsrv' => ['dblib', 'sqlsrv', 'mssql'],
	];

	/**
	 * Connections.
	 *
	 * @var array
	 */
	protected $connectionClasses =
	[
		'firebird' => FirebirdConnection::class,
		'mysql'    => MySQLConnection::class,
		'oracle'   => OracleConnection::class,
		'pgsql'    => PostgresConnection::class,
		'sqlite'   => SQLiteConnection::class,
		'sqlsrv'   => SQLServerConnection::class,
	];

	/**
	 * Query compilers.
	 *
	 * @var array
	 */
	protected $queryCompilerClasses =
	[
		'firebird' => FirebirdCompiler::class,
		'mysql'    => MySQLCompiler::class,
		'nuodb'    => NuoDBCompiler::class,
		'oracle'   => OracleCompiler::class,
		'pgsql'    => PostgresCompiler::class,
		'sqlite'   => SQLiteCompiler::class,
		'sqlsrv'   => SQLServerCompiler::class,
	];

	/**
	 * Query builder helpers.
	 *
	 * @var array
	 */
	protected $queryBuilderHelperClasses =
	[
		'pgsql' => PostgresHelper::class,
	];

	/**
	 * Returns the normalized driver name.
	 *
	 * @param  string $driver Driver name
	 * @return string
	 */
	protected function normalizeDriverName(string $driver): string
	{
		foreach($this->driverAliases as $normalized => $aliases)
		{
			if(in_array($driver, $aliases))
			{
				return $normalized;
			}
		}

		return $driver;
	}

	/**
	 * Returns the connection class.
	 *
	 * @param  string $driver Driver name
	 * @return string
	 */
	protected function getConnectionClass(string $driver): string
	{
		return $this->connectionClasses[$driver] ?? Connection::class;
	}

	/**
	 * Retuns the query compiler class.
	 *
	 * @param  string $driver Driver name
	 * @return string
	 */
	protected function getQueryCompilerClass(string $driver): string
	{
		return $this->queryCompilerClasses[$driver] ?? Compiler::class;
	}

	/**
	 * Retuns the query builder helper class.
	 *
	 * @param  string $driver Driver name
	 * @return string
	 */
	protected function getQueryBuilderHelperClass(string $driver): string
	{
		return $this->queryBuilderHelperClasses[$driver] ?? Helper::class;
	}

	/**
	 * Sets a driver alias.
	 *
	 * @param string       $driver Driver name
	 * @param array|string $alias  Alias or array of aliases
	 */
	public function setDriverAlias(string $driver, $alias): void
	{
		$this->driverAliases[$driver] = (array) $alias;
	}

	/**
	 * Sets a connection class.
	 *
	 * @param string $driver Driver name
	 * @param string $class  Connection class
	 */
	public function setConnectionClass(string $driver, string $class): void
	{
		$this->connectionClasses[$driver] = $class;
	}

	/**
	 * Sets a query compiler class.
	 *
	 * @param string $driver Driver name
	 * @param string $class  Query compiler class
	 */
	public function setQueryCompilerClass(string $driver, string $class): void
	{
		$this->queryCompilerClasses[$driver] = $class;
	}

	/**
	 * Sets a query builder helper class.
	 *
	 * @param string $driver Driver name
	 * @param string $class  Query builder helper class
	 */
	public function setQueryBuilderHelperClass(string $driver, string $class): void
	{
		$this->queryBuilderHelperClasses[$driver] = $class;
	}

	/**
	 * Connects to the chosen database and returns the connection.
	 *
	 * @param  string                                $connectionName Connection name
	 * @return \mako\database\connections\Connection
	 */
	protected function connect(string $connectionName): Connection
	{
		if(!isset($this->configurations[$connectionName]))
		{
			throw new DatabaseException(vsprintf('[ %s ] has not been defined in the database configuration.', [$connectionName]));
		}

		$config = $this->configurations[$connectionName];

		$driver = $this->normalizeDriverName(explode(':', $config['dsn'], 2)[0]);

		$compiler = $this->getQueryCompilerClass($driver);

		$helper = $this->getQueryBuilderHelperClass($driver);

		$connection = $this->getConnectionClass($driver);

		return new $connection($connectionName, $compiler, $helper, $config);
	}

	/**
	 * Adds a database connection to the connection manager.
	 *
	 * @param  \mako\database\connections\Connection $connection Database connection
	 * @return \mako\database\ConnectionManager
	 */
	public function setConnection(Connection $connection): ConnectionManager
	{
		$this->connections[$connection->getName()] = $connection;

		return $this;
	}

	/**
	 * Clears the query log of every connection.
	 */
	public function clearLogs(): void
	{
		foreach($this->connections as $connection)
		{
			$connection->clearLog();
		}
	}

	/**
	 * Returns the query log for all connections.
	 *
	 * @param  bool  $groupedByConnection Group logs by connection?
	 * @return array
	 */
	public function getLogs(bool $groupedByConnection = true): array
	{
		$logs = [];

		if($groupedByConnection)
		{
			foreach($this->connections as $connection)
			{
				$logs[$connection->getName()] = $connection->getLog();
			}
		}
		else
		{
			foreach($this->connections as $connection)
			{
				$logs = array_merge($logs, $connection->getLog());
			}
		}

		return $logs;
	}
}
