<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor;

use \mako\core\Config;
use \mako\core\Package;
use \mako\reactor\io\Input;
use \mako\reactor\io\Output;
use \Exception;
use \ReflectionClass;
use \RuntimeException;
use \ReflectionException;

/**
 * Reactor core class.
 *
 * @author  Frederic G. Østby
 */

class Reactor
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Output.
	 * 
	 * @var \mako\reactor\io\Output;
	 */

	protected $output;

	/**
	 * Input.
	 * 
	 * @var \mako\reactor\io\Input;
	 */

	protected $input;

	/**
	 * Reactor core tasks.
	 *
	 * @var array
	 */

	protected $coreTasks = 
	[
		'app'     => '\mako\reactor\tasks\App',
		'mako'    => '\mako\reactor\tasks\Mako',
		'migrate' => '\mako\reactor\tasks\Migrate',
	];

	/**
	 * Global reactor options.
	 * 
	 * @var array
	 */

	protected $globalOptions = 
	[
		['--env', 'Allows you to override the default environment.'],
		['--database', 'Allows you to override the default database connection.'],
		['--hush', 'Disables all output'],
	];

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
		$this->output = new Output();

		$this->input = new Input($this->output);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sets up the reactor environment and runs the commands.
	 *
	 * @access  public
	 * @param   array   $arguments  Arguments
	 */

	public function run($arguments)
	{
		// Override environment?

		$env = $this->input->param('env', false);

		if($env !== false)
		{
			putenv('MAKO_ENV=' . $env);
		}

		// Override default database?

		$database = $this->input->param('database', false);

		if($database !== false)
		{
			Config::set('database.default', $database);
		}

		// Disable output?

		$hush = $this->input->param('hush', false);

		if($hush !== false)
		{
			$this->output->setVerbosity(Output::VERBOSITY_QUIET);

			$this->output->stderr()->setVerbosity(Output::VERBOSITY_QUIET);
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

		$this->output->nl();

		$this->task($arguments);

		$this->output->nl();
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
		// Print basic usage info

		$this->output->writeln('<yellow>Usage:</yellow>');

		$this->output->nl();

		$this->output->writeln('<blue>php reactor <action> [arguments] [options]</blue>');

		$this->output->nl();

		// Print list of global options

		$this->output->writeln('<yellow>Global options:</yellow>');

		$this->output->nl();

		$this->output->table(['Option', 'Description'], $this->globalOptions);

		$this->output->nl();

		// Print task list

		$this->output->writeln('<yellow>Available actions:</yellow>');

		$this->output->nl();

		$tasks = [];

		foreach($this->findTasks() as $task)
		{
			$reflection = new ReflectionClass($task);

			if($reflection->isAbstract())
			{
				continue;
			}

			$taskInfo = $task::getTaskInfo();

			if(empty($taskInfo))
			{
				continue;
			}

			$prefix = (strpos($task, '\app') === 0 || strpos($task, '\mako') === 0) ? '' : strstr(trim($task, '\\'), '\\', true) . '::';

			foreach($taskInfo as $key => $info)
			{
				$tasks[] = [strtolower($prefix . $reflection->getShortName()) . '.' . $key, $info['description']];
			}
		}

		$this->output->table(['Action', 'Description'], $tasks);

		$this->output->nl();

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
			$this->output->error(vsprintf("The [ %s ] task does not exist.", [end((explode('\\', $task)))]));

			return false;
		}

		if($task->isSubClassOf('\mako\reactor\Task') === false)
		{
			$this->output->error(vsprintf("The [ %s ] task needs to extend the mako\\reactor\Task class.", [$task]));

			return false;
		}

		return $task->newInstance($this->input, $this->output);
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
				call_user_func_array([$task, $method], array_slice($arguments, 1));
			}
		}
		else
		{
			$this->listTasks();
		}
	}
}

