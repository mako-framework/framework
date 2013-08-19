<?php

namespace mako\reactor;

use \mako\Config;
use \mako\Package;
use \mako\reactor\CLI;
use \Exception;
use \ReflectionClass;
use \RuntimeException;
use \ReflectionException;

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
	 * Reactor core tasks.
	 *
	 * @var array
	 */

	protected $coreTasks = array
	(
		'app'     => '\mako\reactor\tasks\App',
		'console' => '\mako\reactor\tasks\Console',
		'migrate' => '\mako\reactor\tasks\Migrate',
		'server'  => '\mako\reactor\tasks\Server',
	);

	/**
	 * Global reactor options.
	 * 
	 * @var array
	 */

	protected $globalOptions = array
	(
		'env'      => 'Allows you to override the default environment.',
		'database' => 'Allows you to override the default database connection.',
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
		// Override environment?

		$env = $this->cli->param('env', false);

		if($env !== false)
		{
			putenv('MAKO_ENV=' . $env);
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
	 * Finds all tasks.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function findTasks()
	{
		$tasks = $this->coreTasks;

		// Find all application tasks

		$appTasks = glob(MAKO_APPLICATION_PATH . '/tasks/*.php');

		if(is_array($appTasks))
		{
			foreach($appTasks as $task)
			{
				$tasks[] = '\app\tasks\\' . basename($task, '.php');
			}
		}

		// Find all package tasks

		$packages = glob(MAKO_PACKAGES_PATH . '/*');

		if(is_array($packages))
		{
			foreach($packages as $package)
			{
				if(is_dir($package))
				{
					$packageTasks = glob($package . '/tasks/*.php');

					if(is_array($packageTasks))
					{
						foreach($packageTasks as $task)
						{
							$tasks[] = '\\' . basename($package) . '\tasks\\' . basename($task, '.php');
						}
					}
				}
			}
		}

		return $tasks;
	}

	/**
	 * Lists all available tasks.
	 * 
	 * @access  protected
	 */

	protected function listTasks()
	{
		// Loop through tasks and fetch info

		$info = array();

		foreach($this->findTasks() as $task)
		{
			$reflection = new ReflectionClass($task);

			if($reflection->isAbstract())
			{
				continue;
			}

			$taskInfo = $reflection->getProperty('taskInfo');

			$taskInfo->setAccessible(true);

			$taskInfo = $taskInfo->getValue();

			if(empty($taskInfo))
			{
				continue;
			}
			
			$prefix = (strpos($task, '\app') === 0 || strpos($task, '\mako') === 0) ? '' : strstr(trim($task, '\\'), '\\', true) . '::';

			$info[strtolower($prefix . $reflection->getShortName())] = $taskInfo;
		}

		// Find longest task name

		$longestName = 0;

		foreach($info as $taskName => $taskInfo)
		{
			foreach($taskInfo as $actionName => $actionInfo)
			{
				$length = strlen($taskName . '.' . $actionName) + 2;

				if($length > $longestName)
				{
					$longestName = $length;
				}
			}
		}

		// Display available tasks with descriptions

		$this->cli->stdout('Usage:', 'yellow');

		$this->cli->newLine();

		$this->cli->stdout(' php reactor <action> [arguments] [options]');

		$this->cli->newLine();

		$this->cli->stdout('Global options:', 'yellow');

		$this->cli->newLine();

		foreach($this->globalOptions as $optionName => $optionDescription)
		{
			$this->cli->stdout(' ' . $this->cli->color(str_pad('--' . $optionName, $longestName, ' '), 'green') . $optionDescription);
		}

		$this->cli->newLine();

		$this->cli->stdout('Available actions:', 'yellow');

		$this->cli->newLine();

		foreach($info as $taskName => $taskInfo)
		{
			foreach($taskInfo as $actionName => $actionInfo)
			{
				$actionName = $actionName === 'run' ? '' : '.' . $actionName;

				$this->cli->stdout(' ' . $this->cli->color(str_pad($taskName . $actionName, $longestName, ' '), 'green') . $actionInfo['description']);
			}

			$this->cli->newline();
		}

		exit;
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
			$task = $this->coreTasks[$task];
		}
		else
		{
			if(strpos($task, '::'))
			{
				list($package, $task) = explode('::', $task, 2);

				Package::init($package);

				$task = '\\' . $package . '\tasks\\' . $task;
			}
			else
			{
				$task = '\app\tasks\\' . $task;
			}
		}

		try
		{
			$task = new ReflectionClass($task);
		}
		catch(ReflectionException $e)
		{
			$this->cli->stderr(vsprintf("The '%s' task does not exist.", array(end((explode('\\', $task))))));

			return false;
		}

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
			$this->listTasks();
		}
	}
}

/** -------------------- End of file -------------------- **/