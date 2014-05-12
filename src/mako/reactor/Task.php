<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor;

use \mako\utility\Str;
use \mako\reactor\io\Input;
use \mako\reactor\io\Output;
use \ReflectionClass;
use \ReflectionMethod;

/**
 * Base task.
 *
 * @author  Frederic G. Østby
 */

abstract class Task
{
	/**
	 * Input
	 * 
	 * @var \mako\reactor\io\Input
	 */

	protected $input;

	/**
	 * Output
	 * 
	 * @var \mako\reactor\io\Output
	 */

	protected $output;

	/**
	 * Task information.
	 * 
	 * @var array
	 */

	protected static $taskInfo = [];

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\reactor\io\Input   $input   Input
	 * @param   \mako\reactor\io\Output  $output  Output
	 */

	public function __construct(Input $input, Output $output)
	{
		$this->input = $input;

		$this->output = $output;

		// Display task info?

		if($this->input->param('task-info', false))
		{
			$this->displayTaskInfo();
		}
	}

	/**
	 * Gets the task info.
	 * 
	 * @access  public
	 * @return  array
	 */

	public static function getTaskInfo()
	{
		return static::$taskInfo;
	}

	/**
	 * Displays task info.
	 * 
	 * @access  public
	 */

	protected function displayTaskInfo()
	{
		$className = get_class($this);

		$prefix = (strpos($className, 'app') === 0 || strpos($className, 'mako') === 0) ? '' : strstr(trim($className, '\\'), '\\', true) . '::';

		$taskName = strtolower(end((explode('\\', $className))));

		$this->output->writeln('<yellow>Available actions in the "' . $prefix . $taskName . '" task:</yellow>');

		$this->output->nl();

		foreach(static::$taskInfo as $action => $info)
		{
			$this->output->writeln('<green>' . $prefix . $taskName . '.' . $action . '</green> ' . $info['description']);

			$this->output->nl();

			if(!empty($info['options']))
			{
				$options = [];

				foreach($info['options'] as $name => $description)
				{
					$options[] = ['--' . $name, $description];
				}

				$this->output->table(['Option', 'Description'], $options);

				$this->output->nl();
			}
		}

		exit;
	}

	/**
	 * Default action.
	 * 
	 * @access  public
	 */

	public function run()
	{
		$this->displayTaskInfo();
	}

	/**
	 * Display list of available actions if non-existant method is called.
	 *
	 * @access  public
	 * @param  string  $name       Method name
	 * @param  array   $arguments  Method arguments
	 */

	public function __call($name, $arguments)
	{
		$this->output->error(vsprintf("Unknown task action [ %s ].", [$name]));

		$this->output->nl();

		$this->displayTaskInfo();
	}
}