<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor\tasks;

use \Exception;

use \mako\reactor\Task;
use \mako\reactor\io\Input;
use \mako\reactor\io\Output;
use \mako\syringe\Container;

/**
 * Database migrations.
 *
 * @author  Frederic G. Østby
 */

class Migrate extends Task
{
	/**
	 * IoC container instance.
	 * 
	 * @var \mako\syringe\Container
	 */

	protected $container;

	/**
	 * Application instance.
	 * 
	 * @var \mako\application\Application
	 */

	protected $application;

	/**
	 * File system instance.
	 * 
	 * @var \mako\file\FileSystem
	 */

	protected $fileSystem;

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
		'up' => 
		[
			'description' => 'Runs all outstanding migrations.',
		],
		'down' => 
		[
			'description' => 'Rolls back the last batch of migrations.',
			'options'     => 
			[
				'batches' => 'Number of batches to roll back.',
			],
		],
		'reset' => 
		[
			'description' => 'Rolls back all migrations.',
			'options'     => 
			[
				'force' => 'Force the schema reset?',
			],
		],
		'create' => 
		[
			'description' => 'Creates a new migration.',
			'options'     => 
			[
				'package'     => 'Package name.',
				'description' => 'Migration description.',
			],
		],
	];

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\reactor\io\Input   $input      Input
	 * @param   \mako\reactor\io\Output  $output     Output
	 * @param   \mako\syringe\Containe   $container  IoC container instance
	 */

	public function __construct(Input $input, Output $output, Container $container)
	{
		parent::__construct($input, $output);

		$this->container = $container;

		$this->application = $container->get('app');

		$this->fileSystem = $container->get('fileSystem');
	}

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
			$this->connection = $this->container->get('database')->connection();
		}

		return $this->connection;
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

	protected function table()
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

		$this->output->table(['Migration', 'Description'], $tableBody);
	}

	/**
	 * Displays the number of outstanding migrations.
	 *
	 * @access  public
	 */

	public function status()
	{
		$migrations = $this->getOutstanding();

		if(($count = count($migrations)) > 0)
		{
			$message = $count === 1 ? 'There is %s outstanding migration:' : 'There are %s outstanding migrations:';

			$this->output->writeln(vsprintf($message, ['<yellow>' . $count . '</yellow>']) . PHP_EOL);

			$this->outputMigrationList($migrations);
		}
		else
		{
			$this->output->writeln('<green>There are no outstanding migrations.</green>');
		}
	}

	/**
	 * Returns a migration instance.
	 *
	 * @access  protected
	 * @param   StdClass                         $migration  Migration object
	 * @return  \mako\reactor\migrate\Migration
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

			$this->table()->insert(['batch' => $batch, 'package' => $migration->package, 'version' => $migration->version]);
		}

		$this->output->writeln('Ran the following migrations:' . PHP_EOL);

		$this->outputMigrationList($migrations);
	}

	/**
	 * Returns an array of migrations to roll back.
	 * 
	 * @access  protected
	 * @param   int        $batches  Number of batches to roll back
	 * @return  array
	 */

	protected function getBatch($batches)
	{
		$query = $this->table();

		if($batches > 0)
		{
			$query->where('batch', '>', ($this->table()->max('batch') - $batches));
		}

		return $query->select(['version', 'package'])->orderBy('version', 'desc')->all();
	}

	/**
	 * Rolls back the n last migration batches.
	 *
	 * @access  public
	 */

	public function down()
	{
		$migrations = $this->getBatch($this->input->param('batches', 1));

		if(empty($migrations))
		{
			$this->output->writeln('<blue>There are no migrations to roll back.</blue>');
		}

		foreach($migrations as $migration)
		{
			$this->resolve($migration)->down();

			$this->table()->where('version', '=', $migration->version)->delete();
		}

		$this->output->writeln('Rolled back the following migrations:' . PHP_EOL);

		$this->outputMigrationList($migrations);
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
	 */

	public function create()
	{
		$package = $this->input->param('package');

		// Get file path and namespace

		if(empty($package))
		{
			$namespace = $this->application->getNamespace() . '\\migrations';

			$path = $this->application->getPath() . '/migrations/';
		}
		else
		{
			$package = $this->application->getPackage($package);

			$namespace = $package->getClassNamespace() . '\\migrations';

			$path = $package->getPath() . '/src/migrations/';
		}

		$path .= 'Migration_' . ($version = gmdate('YmdHis')) . '.php';

		// Create migration

		$description = str_replace("'", "\'", $this->input->param('description'));

		$search = ['{{namespace}}', '{{version}}', '{{description}}'];

		$replace = [$namespace, $version, $description];

		$migration = str_replace($search, $replace, $this->fileSystem->getContents(__DIR__ . '/migrate/migration.tpl'));

		try
		{
			$this->fileSystem->putContents($path, $migration);
		}
		catch(Exception $e)
		{
			return $this->output->error('Failed to create migration. Make sure that the migrations directory is writable.');
		}

		$this->output->writeln(vsprintf('Migration created at "%s".', [$path]));
	}
}