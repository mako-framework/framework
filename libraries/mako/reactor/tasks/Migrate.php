<?php

namespace mako\reactor\tasks;

use \StdClass;
use \mako\CLI;
use \mako\Mako;
use \mako\Database;

/**
* Database migrations.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Migrate extends \mako\reactor\Task
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Database connection.
	*
	* @var mako\database\Connection
	*/

	protected $connection;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	*/

	public function __construct()
	{
		$this->connection = Database::connection();
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Returns a query builder instance.
	*
	* @access  protected
	* @return  mako\database\Query
	*/

	protected function table()
	{
		return $this->connection->table('mako_migrations');
	}

	/**
	* Returns the timestamp part of the migration name.
	*
	* @access  protected
	* @param   string     $name  Migration name
	* @return  int
	*/

	protected function timestamp($name)
	{
		return array_shift(explode('_', $name, 2));
	}

	/**
	* Returns array of all outstanding migrations.
	*
	* @access  protected
	*/

	protected function getOutstanding()
	{
		$migrations = array();

		// Get application migrations

		$files = glob(MAKO_APPLICATION . '/migrations/*_*.php');

		foreach($files as $file)
		{
			$migration = new StdClass();
			
			$migration->name    = basename($file, '.php');
			$migration->package = '';

			$migrations[] = $migration;
		}

		// Get package migrations

		$packages = glob(MAKO_PACKAGES . '/*');

		foreach($packages as $package)
		{
			if(is_dir($package))
			{
				$files = glob($package . '/migrations/*_*.php');

				foreach($files as $file)
				{
					$migration = new StdClass();

					$migration->name    = basename($file, '.php');
					$migration->package = basename($package);

					$migrations[] = $migration;
				}
			}
		}

		// Remove migrations that have already been ran

		$ran = array();

		foreach($this->table()->all() as $migration)
		{
			$ran[] = $migration->name;
		}

		foreach($migrations as $key => $value)
		{
			if(in_array($value->name, $ran))
			{
				unset($migrations[$key]);
			}
		}

		// Sort migrations so that they get executed in the right order

		usort($migrations, function($a, $b)
		{
			return strcmp($a->name, $b->name);
		});

		return $migrations;
	}

	/**
	* Returns a migration instance.
	*
	* @access  protected
	* @param   StdClass   $migration  Migration object
	* @return  Migration
	*/

	protected function resolve($migration)
	{
		if(!empty($migration->package))
		{
			$file = $migration->package . '::' . $migration->name;
		}
		else
		{
			$file = $migration->name;
		}

		include Mako::path('migrations', $file);

		$class = '\Migration_' . $this->timestamp($migration->name);

		return new $class();
	}

	/**
	* Runs all outstanding migrations.
	*
	* @access  public
	*/

	public function run()
	{
		$migrations = $this->getOutstanding();

		if(empty($migrations))
		{
			return CLI::stdout('There are no outstanding migrations.');
		}

		$batch = $this->table()->max('batch') + 1;

		foreach($migrations as $migration)
		{
			$this->resolve($migration)->up();

			$this->table()->insert(array('batch' => $batch, 'package' => $migration->package, 'name' => $migration->name));

			CLI::stdout('Ran the ' . $migration->name . ' migration.');
		}
	}

	/**
	* Rolls back the last migration batch.
	*
	* @access  public
	*/

	public function rollback()
	{
		$migrations = $this->table()
			->where('batch', '=', $this->table()->max('batch'))
			->orderBy('name', 'desc')
			->all(array('name', 'package'));

		if(empty($migrations))
		{
			CLI::stdout('There are no migrations to roll back.');

			return false;
		}

		foreach($migrations as $migration)
		{
			$this->resolve($migration)->down();

			$this->table()->where('name', '=', $migration->name)->delete();

			CLI::stdout('Rolled back the ' . $migration->name . ' migration.');
		}

		return true;
	}

	/**
	* Rolls back all migrations.
	*
	* @access  public
	*/

	public function reset()
	{
		while($this->rollback())
		{
			// Rolling back untill there's no more migrations
		}
	}

	/**
	* Creates the migration log table.
	*
	* @access  public
	*/

	public function install()
	{
		/*CREATE TABLE `mako_migrations` (
		  `batch` int(10) unsigned NOT NULL,
		  `package` varchar(255) NOT NULL,
		  `name` varchar(255) NOT NULL
		);*/

		CLI::stderr('Migration installation has not been implemented yet.');
	}

	/**
	* Creates a migration template.
	*
	* @access  public
	* @param   string  $name  Migration name
	*/

	public function create($name = '')
	{
		if(empty($name))
		{
			return CLI::stderr('You need to provide a migration name.');
		}

		// Get file path

		$timestamp = gmdate('YmdHis');

		if(stripos($name, '::') !== false)
		{
			$name = explode('::', $name, 2);

			$name = implode('::', array($name[0], $timestamp . '_' . $name[1]));
		}
		else
		{
			$name = $timestamp . '_' . $name;
		}

		$file = Mako::path('migrations', $name);

		// Create migration

		$migration = str_replace('{{timestamp}}', $timestamp, file_get_contents(__DIR__ . '/migrate/migration.tpl'));

		if(!@file_put_contents($file, $migration))
		{
			return CLI::stderr('Failed to create migration. Make sure that the migrations directory is writable.');
		}

		CLI::stdout('Migration created!');
	}
}

/** -------------------- End of file --------------------**/