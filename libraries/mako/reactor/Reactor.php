<?php

namespace mako\reactor;

use \mako\Config;
use \mako\Package;
use \mako\reactor\CLI;
use \Exception;
use \ReflectionClass;
use \RuntimeException;

/**
 * Reactor core class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Reactor
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * CLI
	 * 
	 * @var \mako\reactor\CLI
	 */

	protected $cli;

	/**
	 * Mako Reactor core tasks.
	 *
	 * @var array
	 */

	protected $coreTasks = array
	(
		'console' => 'Console',
		'migrate' => 'Migrate',
		'server'  => 'Server',
	);

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
		$this->cli = new CLI();
	}

	/**
	 * Factory method making method chaining possible right off the bat.
	 * 
	 * @access  public
	 */

	public static function factory()
	{
		return new static();
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sets up the CLI environment and runs the commands.
	 *
	 * @access  public
	 * @param   array   $arguments  Arguments
	 */

	public function run($arguments)
	{
		// List the available tasks

		if($this->cli->param('list-tasks', false))
		{
			$this->cli->stdout();

			$this->help();

			$this->cli->stdout();

			return;
		}

		// Override environment?

		$env = $this->cli->param('env', false);

		if($env !== false)
		{
			$_SERVER['MAKO_ENV'] = $env;
		}

		// Override default database?

		$database = $this->cli->param('database', false);

		if($database !== false)
		{
			Config::set('database.default', $database);
		}

		// Remove options from argument list so that it doesnt matter what order they come in

		foreach($arguments as $key => $value)
		{
			if(substr($value, 0, 2) == '--')
			{
				unset($arguments[$key]);
			}
		}

		// Run task

		$this->cli->stdout();

		$this->task($arguments);

		$this->cli->stdout();
	}

	/**
	 * Help command that lists all available tasks.
	 * 
	 * @access  public
	 */

	protected function help()
	{
		// Find application tasks

		$appTasks = array_map(function($task)
		{
			return basename($task, '.php');
		}, glob(MAKO_APPLICATION_PATH . '/tasks/*.php'));

		// Find package tasks

		$packageTasks = array();

		$packages = glob(MAKO_PACKAGES_PATH . '/*');

		foreach($packages as $package)
		{
			if(is_dir($package))
			{
				$tasks = glob($package . '/tasks/*.php');

				foreach($tasks as $task)
				{
					$packageTasks[] = basename($package) . '::' . basename($task, '.php');
				}
			}
		}
		
		// Print list of available tasks

		$this->cli->stdout($this->cli->color('Mako Framework', 'green') . ' version ' . $this->cli->color(MAKO_VERSION, 'yellow') . PHP_EOL);

		$this->cli->stdout('Available core tasks:' . PHP_EOL);

		foreach($this->coreTasks as $task)
		{
			$this->cli->stdout(str_repeat(' ', 4) . $this->cli->color('*', 'yellow') . ' ' . strtolower($task));
		}

		if(!empty($appTasks))
		{
			$this->cli->stdout(PHP_EOL . 'Available application tasks:' . PHP_EOL);

			foreach($appTasks as $task)
			{
				$this->cli->stdout(str_repeat(' ', 4) . $this->cli->color('*', 'yellow') . ' ' . $task);
			}
		}

		if(!empty($packageTasks))
		{
			$this->cli->stdout(PHP_EOL . 'Available package tasks:' . PHP_EOL);

			foreach($packageTasks as $task)
			{
				$this->cli->stdout(str_repeat(' ', 4) . $this->cli->color('*', 'yellow') . ' ' . $task);
			}
		}
	}

	/**
	 * Returns an instance of the chosen task.
	 *
	 * @access  protected
	 * @param   string     $task  Task name
	 * @return  mixed
	 */

	protected function resolve($task)
	{
		if(isset($this->coreTasks[$task]))
		{
			$task = '\mako\reactor\tasks\\' . $this->coreTasks[$task];
		}
		else
		{
			$file = mako_path('tasks', $task);

			if(!file_exists($file))
			{
				$this->cli->stderr(vsprintf("The '%s' task does not exist.", array($task)));

				return false;
			}

			if(strpos($task, '::'))
			{
				list($package, $task) = explode('::', $task, 2);

				Package::init($package);
			}

			include $file;
		}

		$task = new ReflectionClass($task);

		if($task->isSubClassOf('\mako\reactor\Task') === false)
		{
			$this->cli->stderr(vsprintf("The '%s' task needs to extend the mako\\reactor\Task class.", array($task)));

			return false;
		}

		return $task->newInstance($this->cli);
	}

	/**
	 * Runs the chosen task.
	 *
	 * @access  protected
	 * @param   array      $arguments  Arguments
	 */

	protected function task($arguments)
	{
		if(!empty($arguments))
		{
			if(strpos($arguments[0], '.') !== false)
			{
				list($task, $method) = explode('.', $arguments[0], 2);
			}
			else
			{
				$task   = $arguments[0];
				$method = 'run';
			}

			if(($task = $this->resolve($task)) !== false)
			{
				call_user_func_array(array($task, $method), array_slice($arguments, 1));
			}
		}
		else
		{
			$this->help();
		}
	}
}

/** -------------------- End of file --------------------**/