<?php

namespace mako\reactor;

use \mako\CLI;
use \mako\Config;
use \mako\Package;
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
		'package' =>  'Package',
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
		// Override environment?

		$env = CLI::param('env', false);

		if($env !== false)
		{
			$_SERVER['MAKO_ENV'] = $env;
		}

		// Override default database?

		$database = CLI::param('database', false);

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

		CLI::stdout();

		static::task($arguments);

		CLI::stdout();
	}

	/**
	* Returns an instance of the chosen task.
	*
	* @access  protected
	* @param   string     $task  Task name
	* @return  mixed
	*/

	protected static function resolve($task)
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
				CLI::stderr(vsprintf("The '%s' task does not exist.", array($task)));

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
			CLI::stderr(vsprintf("The '%s' task needs to extend the mako\\reactor\Task class.", array($task)));

			return false;
		}

		return $task->newInstance();
	}

	/**
	* Runs the chosen task.
	*
	* @access  protected
	* @param   array      $arguments  Arguments
	*/

	protected static function task($arguments)
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

			if(($task = static::resolve($task)) !== false)
			{
				call_user_func_array(array($task, $method), array_slice($arguments, 1));
			}
		}
		else
		{
			CLI::stderr('You need to provide a task name.');
		}
	}
}

/** -------------------- End of file --------------------**/