<?php

namespace mako\reactor\tasks;

use \mako\Database;
use \mako\reactor\CLI;
use \StdClass;

/**
 * Database migrations.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Migrate extends \mako\reactor\Task
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Database connection.
	 *
	 * @var \mako\database\Connection
	 */

	protected $connection;

	/**
	 * Task information.
	 * 
	 * @var array
	 */

	protected static $taskInfo = array
	(
		'status' => array
		(
			'description' => 'Checks if there are any outstanding migrations.'
		),
		'create' => array
		(
			'description' => 'Creates a new migration.',
		),
		'up' => array
		(
			'description' => 'Runs all outstanding migrations.',
		),
		'down' => array
		(
			'description' => 'Rolls back the last batch of migrations.'
		),
		'reset' => array
		(
			'description' => 'Rolls back all migrations.',
			'options'     => array
			(
				'force' => 'Force the schema reset?'
			),
		),
	);

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\reactor\CLI  $cli  CLI
	 */

	public function __construct(CLI $cli)
	{
		parent::__construct($cli);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the database connection.
	 * 
	 * @access  protected
	 * @return  \mako\database\Connection
	 */

	protected function connection()
	{
		if(empty($this->connection))
		{
			$this->connection = Database::connection();
		}

		return $this->connection;
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @access  protected
	 * @return  \mako\database\Query
	 */

	protected function table()
	{
		return $this->connection()->table('mako_migrations');
	}

	/**
	 * Returns array of all outstanding migrations.
	 *
	 * @access  protected
	 * @return  array
	 */

	protected function getOutstanding()
	{
		$migrations = array();

		// Get application migrations

		$files = glob(MAKO_APPLICATION_PATH . '/migrations/*.php');

		if(is_array($files))
		{
			foreach($files as $file)
			{
				$migration = new StdClass();
				
				$migration->version = basename($file, '.php');
				$migration->package = '';

				$migrations[] = $migration;
			}
		}

		// Get package migrations

		$packages = glob(MAKO_PACKAGES_PATH . '/*');

		if(is_array($packages))
		{
			foreach($packages as $package)
			{
				if(is_dir($package))
				{
					$files = glob($package . '/migrations/*.php');

					if(is_array($files))
					{
						foreach($files as $file)
						{
							$migration = new StdClass();

							$migration->version = basename($file, '.php');
							$migration->package = basename($package);

							$migrations[] = $migration;
						}
					}
				}
			}
		}

		// Remove migrations that have already been executed

		foreach($this->table()->all() as $ran)
		{
			foreach($migrations as $key => $migration)
			{
				if($ran->package === $migration->package && $ran->version === $migration->version)
				{
					unset($migrations[$key]);
				}
			}
		}

		// Sort remaining migrations so that they get executed in the right order

		usort($migrations, function($a, $b)
		{
			return strcmp($a->version, $b->version);
		});

		return $migrations;
	}

	/**
	 * Returns an array of migrations to roll back.
	 * 
	 * @access  protected
	 * @param   int        $batches  Number of batches to roll back
	 * @return  array
	 */

	protected function getBatch($batches = 1)
	{
		$query = $this->table();

		if($batches > 0)
		{
			$query->where('batch', '>', ($this->table()->max('batch') - $batches));
		}

		return $query->orderBy('version', 'desc')->all(array('version', 'package'));
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
		$file = $migration->version;

		if(!empty($migration->package))
		{
			$file = $migration->package . '::' . $file;
		}

		include mako_path('migrations', $file);

		$class = '\Migration_' . $migration->version;

		return new $class();
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
		  `version` varchar(255) NOT NULL
		);*/

		$this->cli->stderr('Migration installation has not been implemented yet.');
	}

	/**
	 * Displays the number of outstanding migrations.
	 *
	 * @access  public
	 */

	public function status()
	{
		if(($count = count($this->getOutstanding())) > 0)
		{
			$this->cli->stdout(sprintf(($count === 1 ? 'There is %s outstanding migration.' : 'There are %s outstanding migrations.'), $this->cli->color($count, 'green')));
		}
		else
		{
			$this->cli->stdout('There are no outstanding migrations.', 'yellow');
		}
	}

	/**
	 * Runs all outstanding migrations.
	 *
	 * @access  public
	 */

	public function up()
	{
		$migrations = $this->getOutstanding();

		if(empty($migrations))
		{
			return $this->cli->stdout('There are no outstanding migrations.', 'yellow');
		}

		$batch = $this->table()->max('batch') + 1;

		foreach($migrations as $migration)
		{
			$this->resolve($migration)->up();

			$this->table()->insert(array('batch' => $batch, 'package' => $migration->package, 'version' => $migration->version));

			$name = $migration->version;

			if(!empty($migration->package))
			{
				$name = $migration->package . '::' . $name;
			}

			$this->cli->stdout('Ran the ' . $name . ' migration.');
		}
	}

	/**
	 * Rolls back the n last migration batches.
	 *
	 * @access  public
	 * @param   int     $batches  Number of batches to roll back
	 */

	public function down($batches = 1)
	{
		$migrations = $this->getBatch($batches);

		if(empty($migrations))
		{
			$this->cli->stdout('There are no migrations to roll back.', 'yellow');
		}

		foreach($migrations as $migration)
		{
			$this->resolve($migration)->down();

			$this->table()->where('version', '=', $migration->version)->delete();

			$name = $migration->version;

			if(!empty($migration->package))
			{
				$name = $migration->package . '::' . $name;
			}

			$this->cli->stdout('Rolled back the ' . $name . ' migration.');
		}
	}

	/**
	 * Rolls back all migrations.
	 *
	 * @access  public
	 */

	public function reset()
	{
		if($this->cli->param('force', false) || $this->cli->confirm('Are you sure you want to reset your database?'))
		{
			$this->down(0);
		}
	}

	/**
	 * Creates a migration template.
	 *
	 * @access  public
	 * @param   string  $package  (optional) Package name
	 */

	public function create($package = '')
	{
		// Get file path

		$file = $version = gmdate('YmdHis');

		if(!empty($package))
		{
			$file = $package . '::' . $file;
		}

		$file = mako_path('migrations', $file);

		// Create migration

		$migration = str_replace('{{version}}', $version, file_get_contents(__DIR__ . '/migrate/migration.tpl'));

		if(!@file_put_contents($file, $migration))
		{
			return $this->cli->stderr('Failed to create migration. Make sure that the migrations directory is writable.');
		}

		$this->cli->stdout(sprintf('Migration created at "%s".', $file));
	}
}

/** -------------------- End of file -------------------- **/