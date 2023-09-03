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
use mako\database\query\Result;
use mako\database\query\ResultSet;
use mako\file\FileSystem;
use mako\reactor\Command as BaseCommand;
use mako\syringe\Container;
use ReflectionClass;
use stdClass;

use function basename;
use function strcmp;
use function usort;

/**
 * Base command.
 */
abstract class Command extends BaseCommand
{
	/**
	 * Constructor.
	 */
	public function __construct(
		Input $input,
		Output $output,
		protected Application $application,
		protected FileSystem $fileSystem,
		protected ConnectionManager $database,
		Container $container
	)
	{
		parent::__construct($input, $output);

		$this->container = $container;
	}

	/**
	 * Returns the default connection name of the application.
	 */
	protected function getDefaultConnectionName(): string
	{
		return $this->application->getConfig()->get('database.default');
	}

	/**
	 * Returns the connection name for which we are running migrations.
	 */
	protected function getConnectionName(): string
	{
		return $this->input->getArgument('--database', $this->getDefaultConnectionName());
	}

	/**
	 * Returns a query builder instance.
	 */
	protected function getQuery(): Query
	{
		return $this->database->getConnection($this->getConnectionName())->getQuery()->table('mako_migrations');
	}

	/**
	 * Returns the basename of the migration.
	 */
	protected function getBaseName(string $migration): string
	{
		return basename($migration, '.php');
	}

	/**
	 * Returns all application migrations.
	 */
	protected function findApplicationMigrations(): array
	{
		$migrations = [];

		foreach($this->fileSystem->glob("{$this->application->getPath()}/migrations/*.php") as $migration)
		{
			$migrations[] = (object) ['package' => null, 'version' => $this->getBasename($migration)];
		}

		return $migrations;
	}

	/**
	 * Returns all package migrations.
	 */
	protected function findPackageMigrations(): array
	{
		$migrations = [];

		foreach($this->application->getPackages() as $package)
		{
			foreach($this->fileSystem->glob("{$package->getPath()}/src/migrations/*.php") as $migration)
			{
				$migrations[] = (object) ['package' => $package->getName(), 'version' => $this->getBasename($migration)];
			}
		}

		return $migrations;
	}

	/**
	 * Finds all migrations.
	 */
	protected function findMigrations(): array
	{
		return [...$this->findApplicationMigrations(), ...$this->findPackageMigrations()];
	}

	/**
	 * Returns the fully qualified class name of a migration.
	 */
	protected function getFullyQualifiedMigration(stdClass|Result $migration): string
	{
		if(empty($migration->package))
		{
			return $this->application->getNamespace(true) . "\\migrations\\{$migration->version}";
		}

		return $this->application->getPackage($migration->package)->getClassNamespace(true) . "\\migrations\\{$migration->version}";
	}

	/**
	 * Returns migrations filtered by connection name.
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
	 */
	protected function getMigrated(?int $batches = null): ResultSet
	{
		$query = $this->getQuery();

		if($batches !== null && $batches > 0)
		{
			$query->where('batch', '>', ($this->getQuery()->max('batch') - $batches));
		}

		return $query->select(['version', 'package'])->descending('version')->all();
	}

	/**
	 * Returns an array of all outstanding migrations.
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

			usort($migrations, static fn ($a, $b) => strcmp($a->version, $b->version));
		}

		return $migrations;
	}

	/**
	 * Outputs a migration list.
	 */
	protected function outputMigrationList(array $migrations): void
	{
		$tableBody = [];

		foreach($migrations as $migration)
		{
			$name = $migration->version;

			if(!empty($migration->package))
			{
				$name .= " ({$migration->package})";
			}

			$description = $this->resolve($migration)->getDescription();

			$tableBody[] = [$name, $description];
		}

		$this->table(['Migration', 'Description'], $tableBody);
	}

	/**
	 * Returns a migration instance.
	 */
	protected function resolve(object $migration): Migration
	{
		return $this->container->get($this->getFullyQualifiedMigration($migration));
	}

	/**
	 * Builds the migration wrapper closure.
	 */
	protected function buildMigrationWrapper(object $migration, Migration $migrationInstance, string $method, ?int $batch = null): Closure
	{
		return function () use ($migration, $migrationInstance, $method, $batch): void
		{
			$this->container->call([$migrationInstance, $method]);

			switch($method)
			{
				case 'up':
					$this->getQuery()->insert(['batch' => $batch, 'package' => $migration->package, 'version' => $migration->version]);
					break;
				case 'down':
					$this->getQuery()->where('version', '=', $migration->version)->delete();
					break;

			}
		};
	}

	/**
	 * Executes a migration method.
	 */
	protected function runMigration(object $migration, string $method, ?int $batch = null): void
	{
		$migrationInstance = $this->resolve($migration);

		$migrationWrapper = $this->buildMigrationWrapper($migration, $migrationInstance, $method, $batch);

		if($migrationInstance->useTransaction())
		{
			$migrationInstance->getConnection()->transaction(function () use ($migrationWrapper): void
			{
				$migrationWrapper();
			});

			return;
		}

		$migrationWrapper();
	}
}
