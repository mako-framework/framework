<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database;

use RuntimeException;

use mako\common\ConnectionManager as BaseConnectionManager;
use mako\database\connections\Connection;
use mako\database\connections\DB2 as DB2Connection;
use mako\database\connections\Oracle as OracleConnection;
use mako\database\connections\SQLServer as SQLServerConnection;
use mako\database\query\compilers\Compiler;
use mako\database\query\compilers\DB2 as DB2Compiler;
use mako\database\query\compilers\Firebird as FirebirdCompiler;
use mako\database\query\compilers\MySQL as MySQLCompiler;
use mako\database\query\compilers\NuoDB as NuoDBCompiler;
use mako\database\query\compilers\Oracle as OracleCompiler;
use mako\database\query\compilers\Postgres as PostgresCompiler;
use mako\database\query\compilers\SQLite as SQLiteCompiler;
use mako\database\query\compilers\SQLServer as SQLServerCompiler;
use mako\database\query\helpers\Helper;
use mako\database\query\helpers\Postgres as PostgresHelper;

/**
 * Database connection manager.
 *
 * @author  Frederic G. Østby
 *
 * @method  \mako\database\connections\Connection  connection($connection = null)
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
		'db2'    => ['db2', 'ibm', 'odbc'],
		'sqlsrv' => ['dblib', 'sqlsrv', 'mssql'],
	];

	/**
	 * Connections.
	 *
	 * @var array
	 */
	protected $connectionClasses =
	[
		'db2'    => DB2Connection::class,
		'oracle' => OracleConnection::class,
		'sqlsrv' => SQLServerConnection::class,
	];

	/**
	 * Query compilers.
	 *
	 * @var array
	 */
	protected $queryCompilerClasses =
	[
		'db2'      => DB2Compiler::class,
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
	 * @access  protected
	 * @param   string     $driver  Driver name
	 * @return  string
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
	 * @access  protected
	 * @param   string     $driver  Driver name
	 * @return  string
	 */
	protected function getConnectionClass(string $driver): string
	{
		return $this->connectionClasses[$driver] ?? Connection::class;
	}

	/**
	 * Retuns the query compiler class.
	 *
	 * @access  protected
	 * @param   string     $driver  Driver name
	 * @return  string
	 */
	protected function getQueryCompilerClass(string $driver): string
	{
		return $this->queryCompilerClasses[$driver] ?? Compiler::class;
	}

	/**
	 * Retuns the query builder helper class.
	 *
	 * @access  protected
	 * @param   string     $driver  Driver name
	 * @return  string
	 */
	protected function getQueryBuilderHelperClass(string $driver): string
	{
		return $this->queryBuilderHelperClasses[$driver] ?? Helper::class;
	}

	/**
	 * Sets a driver alias.
	 *
	 * @access  public
	 * @param   string        $driver  Driver name
	 * @param   string|array  $alias   Alias or array of aliases
	 */
	public function setDriverAlias(string $driver, $alias)
	{
		$this->driverAliases[$driver] = (array) $alias;
	}

	/**
	 * Sets a connection class.
	 *
	 * @access  public
	 * @param   string  $driver  Driver name
	 * @param   string  $class   Connection class
	 */
	public function setConnectionClass(string $driver, string $class)
	{
		$this->connectionClasses[$driver] = $class;
	}

	/**
	 * Sets a query compiler class.
	 *
	 * @access  public
	 * @param   string  $driver  Driver name
	 * @param   string  $class   Query compiler class
	 */
	public function setQueryCompilerClass(string $driver, string $class)
	{
		$this->queryCompilerClasses[$driver] = $class;
	}

	/**
	 * Sets a query builder helper class.
	 *
	 * @access  public
	 * @param   string  $driver  Driver name
	 * @param   string  $class   Query builder helper class
	 */
	public function setQueryBuilderHelperClass(string $driver, string $class)
	{
		$this->queryBuilderHelperClasses[$driver] = $class;
	}

	/**
	 * Connects to the chosen database and returns the connection.
	 *
	 * @access  public
	 * @param   string                                 $connectionName  Connection name
	 * @return  \mako\database\connections\Connection
	 */
	protected function connect(string $connectionName): Connection
	{
		if(!isset($this->configurations[$connectionName]))
		{
			throw new RuntimeException(vsprintf("%s(): [ %s ] has not been defined in the database configuration.", [__METHOD__, $connectionName]));
		}

		$config = $this->configurations[$connectionName];

		$driver = $this->normalizeDriverName(explode(':', $config['dsn'], 2)[0]);

		$compiler = $this->getQueryCompilerClass($driver);

		$helper = $this->getQueryBuilderHelperClass($driver);

		$connection = $this->getConnectionClass($driver);

		return new $connection($connectionName, $compiler, $helper, $config);
	}

	/**
	 * Clears the query log of every connection.
	 *
	 * @access  public
	 */
	public function clearLogs()
	{
		foreach($this->connections as $connection)
		{
			$connection->clearLog();
		}
	}

	/**
	 * Returns the query log for all connections.
	 *
	 * @access  public
	 * @param   bool    $groupedByConnection  Group logs by connection?
	 * @return  array
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