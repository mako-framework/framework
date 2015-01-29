<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\application\Application;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\database\ConnectionManager;
use mako\file\FileSystem;
use mako\reactor\Command as BaseCommand;
use mako\syringe\Container;

/**
 * Base command.
 *
 * @author  Frederic G. Østby
 */

abstract class Command extends BaseCommand
{
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
	 * IoC container.
	 *
	 * @var \mako\syringe\Container
	 */

	protected $container;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\cli\input\Input             $input        Input
	 * @param   \mako\cli\output\Output           $output       Output
	 * @param   \mako\application\Application     $application  Application
	 * @param   \mako\file\FileSystem             $fileSystem   File system
	 * @param   \mako\database\ConnectionManager  $database     Connection manager
	 * @param   \mako\syringe\Container           $container     Container
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
	 * Returns the database connection.
	 *
	 * @access  protected
	 * @return  \mako\database\Connection
	 */

	protected function connection()
	{
		return $this->database->connection();
	}

	/**
	 * Returns the basename of the migration.
	 *
	 * @access  protected
	 * @param   string     $migration  Task path
	 * @return  string
	 */

	protected function getBaseName($migration)
	{
		return basename($migration, '.php');
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @access  protected
	 * @return  \mako\database\query\Query
	 */

	protected function builder()
	{
		return $this->connection()->builder()->table('mako_migrations');
	}

	/**
	 * Returns all application migrations.
	 *
	 * @access  protected
	 * @return  array
	 */

	protected function findApplicationMigrations()
	{
		$migrations = [];

		foreach($this->fileSystem->glob($this->application->getPath() . '/migrations/*.php') as $migration)
		{
			$migrations[] = (object) ['package'   => null, 'version' => $this->getBasename($migration)];
		}

		return $migrations;
	}

	/**
	 * Returns all package migrations.
	 *
	 * @access  protected
	 * @return  array
	 */

	protected function findPackageMigrations()
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
	 * Returns an array of all outstanding migrations.
	 *
	 * @access  protected
	 * @return  array
	 */

	protected function getOutstanding()
	{
		$migrations = [];

		// Find all migrations

		$migrations = array_merge($migrations, $this->findApplicationMigrations());

		$migrations = array_merge($migrations, $this->findPackageMigrations());

		// Filter and sort migrations

		if(!empty($migrations))
		{
			foreach($this->builder()->all() as $ran)
			{
				foreach($migrations as $key => $migration)
				{
					if($ran->package === $migration->package && $ran->version === $migration->version)
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

		// Return outstanding migrations

		return $migrations;
	}

	/**
	 * Ouputs a migration list.
	 *
	 * @access  public
	 * @param   array   $migrations  Migrations
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
	 * @access  protected
	 * @param   StdClass                             $migration  Migration object
	 * @return  \mako\database\migrations\Migration
	 */

	protected function resolve($migration)
	{
		if(empty($migration->package))
		{
			$namespace = $this->application->getNamespace(true) . '\\migrations\\';
		}
		else
		{
			$namespace = $this->application->getPackage($migration->package)->getClassNamespace(true) . '\\migrations\\';
		}

		return $this->container->get($namespace . $migration->version);
	}

	/**
	 * Executes a migration method.
	 *
	 * @access  protected
	 * @param   string  $migration  Migration class
	 * @param   string  $method     Migration method
	 */

	protected function runMigration($migration, $method)
	{
		$this->container->call([$this->resolve($migration), $method]);
	}
}