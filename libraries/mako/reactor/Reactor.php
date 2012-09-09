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
 * @copyright  (c) 2008-2012 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Reactor
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	 * Mako Reactor core tasks.
	 *
	 * @var array
	 */

	protected static $coreTasks = array
	(
		'migrate' =>  'Migrate',
	);

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Protected constructor since this is a static class.
	 *
	 * @access  protected
	 */

	protected function __construct()
	{
		// Nothing here
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

	public static function run($arguments)
	{
		$cli = new CLI();

		// Override environment?

		$env = $cli->param('env', false);

		if($env !== false)
		{
			$_SERVER['MAKO_ENV'] = $env;
		}

		// Override default database?

		$database = $cli->param('database', false);

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

		$cli->stdout();

		try
		{
			static::task($cli, $arguments);
		}
		catch(Exception $e)
		{
			$cli->stderr($e->getMessage() . str_repeat(PHP_EOL, 2) . $e->getTraceAsString());
		}

		$cli->stdout();
	}

	/**
	 * Help command that lists all available tasks.
	 * 
	 * @param   mako\reactor\CLI  $cli  CLI
	 * @access  public
	 */

	protected static function help(CLI $cli)
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

		$cli->stdout('Core tasks:' . PHP_EOL);

		foreach(static::$coreTasks as $task)
		{
			$cli->stdout(str_repeat(' ', 4) . $cli->color('*', 'yellow') . ' ' . strtolower($task));
		}

		if(!empty($appTasks))
		{
			$cli->stdout(PHP_EOL . 'Application tasks:' . PHP_EOL);

			foreach($appTasks as $task)
			{
				$cli->stdout(str_repeat(' ', 4) . $cli->color('*', 'yellow') . ' ' . $task);
			}
		}

		if(!empty($packageTasks))
		{
			$cli->stdout(PHP_EOL . 'Package tasks:' . PHP_EOL);

			foreach($packageTasks as $task)
			{
				$cli->stdout(str_repeat(' ', 4) . $cli->color('*', 'yellow') . ' ' . $task);
			}
		}
	}

	/**
	 * Returns an instance of the chosen task.
	 *
	 * @access  protected
	 * @param   mako\reactor\CLI  $cli   CLI
	 * @param   string            $task  Task name
	 * @return  mixed
	 */

	protected static function resolve(CLI $cli, $task)
	{
		if(isset(static::$coreTasks[$task]))
		{
			$task = '\mako\reactor\tasks\\' . static::$coreTasks[$task];
		}
		else
		{
			$file = mako_path('tasks', $task);

			if(!file_exists($file))
			{
				$cli->stderr(vsprintf("The '%s' task does not exist.", array($task)) . PHP_EOL);

				static::help($cli);

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
			$cli->stderr(vsprintf("The '%s' task needs to extend the mako\\reactor\Task class.", array($task)));

			return false;
		}

		return $task->newInstance($cli);
	}

	/**
	 * Runs the chosen task.
	 *
	 * @access  protected
	 * @param   mako\reactor\CLI  $cli        CLI
	 * @param   array             $arguments  Arguments
	 */

	protected static function task(CLI $cli, $arguments)
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

			if(($task = static::resolve($cli, $task)) !== false)
			{
				call_user_func_array(array($task, $method), array_slice($arguments, 1));
			}
		}
		else
		{
			$cli->stderr('You need to provide a task name.' . PHP_EOL);

			static::help($cli);
		}
	}
}

/** -------------------- End of file --------------------**/