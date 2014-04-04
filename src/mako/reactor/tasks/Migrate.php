<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor\tasks;

use \StdClass;

use \mako\core\Application;
use \mako\reactor\io\Input;
use \mako\reactor\io\Output;

/**
 * Database migrations.
 *
 * @author  Frederic G. Østby
 */

class Migrate extends \mako\reactor\Task
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Application instance.
	 * 
	 * @var \mako\core\Application
	 */

	protected $application;

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

	protected static $taskInfo = 
	[
		'status' => 
		[
			'description' => 'Checks if there are any outstanding migrations.'
		],
		'create' => 
		[
			'description' => 'Creates a new migration.',
		],
		'up' => 
		[
			'description' => 'Runs all outstanding migrations.',
		],
		'down' => 
		[
			'description' => 'Rolls back the last batch of migrations.'
		],
		'reset' => 
		[
			'description' => 'Rolls back all migrations.',
			'options'     => 
			[
				'force' => 'Force the schema reset?'
			],
		],
	];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\reactor\io\Input   $input        Input
	 * @param   \mako\reactor\io\Output  $output       Output
	 * @param   \mako\core\Application   $application  Application instance
	 */

	public function __construct(Input $input, Output $output, Application $application)
	{
		parent::__construct($input, $output);

		$this->application = $application;
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
			$this->connection = $this->application->get('database')->connection();
		}

		return $this->connection;
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @access  protected
	 * @return  \mako\database\query\Query
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

		$files = glob($this->application->getApplicationPath() . '/migrations/*.php');

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

		$packages = glob($this->application->getApplicationPath() . '/packages/*');

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

		include mako_path($this->application->getApplicationPath(), 'migrations', $file);

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

		$this->output->error('Migration installation has not been implemented yet.');
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
			$this->output->writeln(vsprintf(($count === 1 ? 'There is %s outstanding migration.' : 'There are %s outstanding migrations.'), array('<yellow>' . $count . '</yellow>')));
		}
		else
		{
			$this->output->writeln('<green>There are no outstanding migrations.</green>');
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
			return $this->output->writeln('<blue>There are no outstanding migrations.</blue>');
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

			$this->output->writeln('Ran the ' . $name . ' migration.');
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
			$this->output->writeln('<blue>There are no migrations to roll back.</blue>');
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

			$this->output->writeln('Rolled back the ' . $name . ' migration.');
		}
	}

	/**
	 * Rolls back all migrations.
	 *
	 * @access  public
	 */

	public function reset()
	{
		if($this->input->param('force', false) || $this->input->confirm('Are you sure you want to reset your database?'))
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

		$file = mako_path($this->application->getApplicationPath(), 'migrations', $file);

		// Create migration

		$migration = str_replace('{{version}}', $version, file_get_contents(__DIR__ . '/migrate/migration.tpl'));

		if(!@file_put_contents($file, $migration))
		{
			return $this->output->error('Failed to create migration. Make sure that the migrations directory is writable.');
		}

		$this->output->writeln(vsprintf('Migration created at "%s".', array($file)));
	}
}