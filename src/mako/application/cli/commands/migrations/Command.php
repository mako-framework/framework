<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use Closure;
use mako\application\Application;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\database\ConnectionManager;
use mako\database\migrations\Migration;
use mako\database\query\Query;
use mako\database\query\ResultSet;
use mako\file\FileSystem;
use mako\reactor\Command as BaseCommand;
use mako\syringe\Container;
use ReflectionClass;

use function array_merge;
use function basename;
use function strcmp;
use function usort;

/**
 * Base command.
 *
 * @author Frederic G. Østby
 */
abstract class Command extends BaseCommand
{
	/**
	 * Make the command strict.
	 *
	 * @var bool
	 */
	protected $isStrict = true;

	/**
	 * Application.
	 *
	 * @var \mako\application\Application
	 */
	protected $application;

	/**
	 * File system.
	 *
	 * @var \mako\file\FileSystem
	 */
	protected $fileSystem;

	/**
	 * Connection manager.
	 *
	 * @var \mako\database\ConnectionManager
	 */
	protected $database;

	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\input\Input            $input       Input
	 * @param \mako\cli\output\Output          $output      Output
	 * @param \mako\application\Application    $application Application
	 * @param \mako\file\FileSystem            $fileSystem  File system
	 * @param \mako\database\ConnectionManager $database    Connection manager
	 * @param \mako\syringe\Container          $container   Container
	 */
	public function __construct(Input $input, Output $output, Application $application, FileSystem $fileSystem, ConnectionManager $database, Container $container)
	{
		parent::__construct($input, $output);

		$this->application = $application;

		$this->fileSystem = $fileSystem;

		$this->database = $database;

		$this->container = $container;
	}

	/**
	 * Returns the default connection name of the application.
	 *
	 * @return string
	 */
	protected function getDefaultConnectionName(): string
	{
		return $this->application->getConfig()->get('database.default');
	}

	/**
	 * Returns the connection name for which we are running migrations.
	 *
	 * @return string
	 */
	protected function getConnectionName(): string
	{
		return $this->input->getArgument('database', $this->getDefaultConnectionName());
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @return \mako\database\query\Query
	 */
	protected function builder(): Query
	{
		return $this->database->connection($this->getConnectionName())->builder()->table('mako_migrations');
	}

	/**
	 * Returns the basename of the migration.
	 *
	 * @param  string $migration Task path
	 * @return string
	 */
	protected function getBaseName($migration): string
	{
		return basename($migration, '.php');
	}

	/**
	 * Returns all application migrations.
	 *
	 * @return array
	 */
	protected function findApplicationMigrations(): array
	{
		$migrations = [];

		foreach($this->fileSystem->glob($this->application->getPath() . '/migrations/*.php') as $migration)
		{
			$migrations[] = (object) ['package' => null, 'version' => $this->getBasename($migration)];
		}

		return $migrations;
	}

	/**
	 * Returns all package migrations.
	 *
	 * @return array
	 */
	protected function findPackageMigrations(): array
	{
		$migrations = [];

		foreach($this->application->getPackages() as $package)
		{
			foreach($this->fileSystem->glob($package->getPath() . '/src/migrations/*.php') as $migration)
			{
				$migrations[] = (object) ['package' => $package->getName(), 'version' => $this->getBasename($migration)];
			}
		}

		return $migrations;
	}

	/**
	 * Finds all migrations.
	 *
	 * @return array
	 */
	protected function findMigrations(): array
	{
		return array_merge($this->findApplicationMigrations(), $this->findPackageMigrations());
	}

	/**
	 * Returns the fully qualified class name of a migration.
	 *
	 * @param  object $migration Migration
	 * @return string
	 */
	protected function getFullyQualifiedMigration($migration): string
	{
		if(empty($migration->package))
		{
			return $this->application->getNamespace(true) . '\\migrations\\' . $migration->version;
		}

		return $this->application->getPackage($migration->package)->getClassNamespace(true) . '\\migrations\\' . $migration->version;
	}

	/**
	 * Returns migrations filtered by connection name.
	 *
	 * @return array
	 */
	protected function getMigrationsFilteredByConnection(): array
	{
		$migrations = $this->findMigrations();

		$connectionName = $this->getConnectionName();

		$defaultConnectionName = $this->getDefaultConnectionName();

		foreach($migrations as $key => $migration)
		{
			$migrationConnectionName = (new ReflectionClass($this->getFullyQualifiedMigration($migration)))
			->newInstanceWithoutConstructor()
			->getConnectionName() ?? $defaultConnectionName;

			if($connectionName !== $migrationConnectionName)
			{
				unset($migrations[$key]);
			}
		}

		return $migrations;
	}

	/**
	 * Returns migrations that have been run.
	 *
	 * @param  int|null                       $batches Number of batches fetch
	 * @return \mako\database\query\ResultSet
	 */
	protected function getMigrated(?int $batches = null): ResultSet
	{
		$query = $this->builder();

		if($batches !== null && $batches > 0)
		{
			$query->where('batch', '>', ($this->builder()->max('batch') - $batches));
		}

		return $query->select(['version', 'package'])->descending('version')->all();
	}

	/**
	 * Returns an array of all outstanding migrations.
	 *
	 * @return array
	 */
	protected function getOutstanding(): array
	{
		$migrations = $this->getMigrationsFilteredByConnection();

		if(!empty($migrations))
		{
			foreach($this->getMigrated() as $migrated)
			{
				foreach($migrations as $key => $migration)
				{
					if($migrated->package === $migration->package && $migrated->version === $migration->version)
					{
						unset($migrations[$key]);
					}
				}
			}

			usort($migrations, function($a, $b)
			{
				return strcmp($a->version, $b->version);
			});
		}

		return $migrations;
	}

	/**
	 * Outputs a migration list.
	 *
	 * @param array $migrations Migrations
	 */
	protected function outputMigrationList(array $migrations)
	{
		$tableBody = [];

		foreach($migrations as $migration)
		{
			$name = $migration->version;

			if(!empty($migration->package))
			{
				$name .= ' (' . $migration->package . ')';
			}

			$description = $this->resolve($migration)->getDescription();

			$tableBody[] = [$name, $description];
		}

		$this->table(['Migration', 'Description'], $tableBody);
	}

	/**
	 * Returns a migration instance.
	 *
	 * @param  object                              $migration Migration meta
	 * @return \mako\database\migrations\Migration
	 */
	protected function resolve($migration): Migration
	{
		return $this->container->get($this->getFullyQualifiedMigration($migration));
	}

	/**
	 * [buildMigrationWrapper description].
	 *
	 * @param  object                              $migration         Migration meta
	 * @param  \mako\database\migrations\Migration $migrationInstance Migration instance
	 * @param  string                              $method            Migration method
	 * @param  int|null                            $batch             Migration batch
	 * @return \Closure
	 */
	protected function buildMigrationWrapper($migration, Migration $migrationInstance, string $method, ?int $batch = null): Closure
	{
		return function() use ($migration, $migrationInstance, $method, $batch)
		{
			$this->container->call([$migrationInstance, $method]);

			switch($method)
			{
				case 'up':
					$this->builder()->insert(['batch' => $batch, 'package' => $migration->package, 'version' => $migration->version]);
					break;
				case 'down':
					$this->builder()->where('version', '=', $migration->version)->delete();
					break;

			}
		};
	}

	/**
	 * Executes a migration method.
	 *
	 * @param object   $migration Migration meta
	 * @param string   $method    Migration method
	 * @param int|null $batch     Batch
	 */
	protected function runMigration($migration, string $method, ?int $batch = null)
	{
		$migrationInstance = $this->resolve($migration);

		$migrationWrapper = $this->buildMigrationWrapper($migration, $migrationInstance, $method, $batch);

		if($migrationInstance->useTransaction())
		{
			$migrationInstance->getConnection()->transaction(function() use ($migrationWrapper)
			{
				$migrationWrapper();
			});

			return;
		}

		$migrationWrapper();
	}
}
