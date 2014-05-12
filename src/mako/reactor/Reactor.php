<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor;

use \Exception;
use \ReflectionClass;
use \RuntimeException;
use \ReflectionException;

use \mako\reactor\Task;
use \mako\reactor\io\Input;
use \mako\reactor\io\Output;
use \mako\syringe\Container;
use \mako\utility\Str;

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
	 * Input.
	 * 
	 * @var \mako\reactor\io\Input;
	 */

	protected $input;

	/**
	 * Output.
	 * 
	 * @var \mako\reactor\io\Output;
	 */

	protected $output;

	/**
	 * IoC container instance.
	 * 
	 * @var \mako\syringe\Container
	 */

	protected $container;

	/**
	 * Avaiable tasks.
	 * 
	 * @var array
	 */

	protected $tasks;

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
	 * @param   \mako\reactor\io\Input   $input      Input instance
	 * @param   \mako\reactor\io\Output  $output     Output instance
	 * @param   \mako\syringe\Container  $container  IoC container instance
	 * @param   array                    $tasks      Available tasks
	 */

	public function __construct(Input $input, Output $output, Container $container, array $tasks)
	{
		$this->input = $input;

		$this->output = $output;

		$this->container = $container;

		$this->tasks = $tasks;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sets up the reactor environment and runs the commands.
	 *
	 * @access  public
	 * @param   array   $arguments  (optional) Arguments
	 */

	public function run(array $arguments = [])
	{
		$arguments = $arguments ?: array_slice($_SERVER['argv'], 1);

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
			$this->container->get('config')->set('database.default', $database);
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

		foreach($this->tasks as $taskKey => $task)
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

			foreach($taskInfo as $actionKey => $info)
			{
				$tasks[] = [$taskKey . '.' . $actionKey, $info['description']];
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
		if(isset($this->tasks[$task]))
		{
			$task = $this->tasks[$task];
		}
		else
		{
			$this->output->error(vsprintf("The [ %s ] task doesn't exist.", [$task]));

			return false;
		}

		$task = $this->container->get($task, [$this->input, $this->output]);

		if(($task instanceof Task) === false)
		{
			$this->output->error('All tasks must extend the mako\reactor\Task class.');

			return false;
		}

		return $task;
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
				call_user_func_array([$task, Str::underscored2camel($method)], array_slice($arguments, 1));
			}
		}
		else
		{
			$this->listTasks();
		}
	}
}