<?php

namespace mako\reactor;

use \mako\String;
use \mako\reactor\CLI;
use \ReflectionClass;
use \ReflectionMethod;

/**
 * Base task.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class Task
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
	 * Task information.
	 * 
	 * @var array
	 */

	protected static $taskInfo = array();

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
		$this->cli = $cli;

		if($this->cli->param('task-info', false))
		{
			$this->displayTaskInfo();

			$this->cli->newLine();

			exit;
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Displays task info.
	 * 
	 * @access  public
	 */

	protected function displayTaskInfo()
	{
		$taskName = strtolower(end((explode('\\', get_class($this)))));

		$longestName = max(array_map('strlen', array_keys(static::$taskInfo))) + strlen($taskName) + 3;

		$this->cli->stdout('Available actions in the "' . $taskName . '" task:', 'yellow');

		$this->cli->newLine();

		foreach(static::$taskInfo as $actionName => $actionInfo)
		{
			$actionName = $actionName === 'run' ? '' : '.' . $actionName;

			$this->cli->stdout(' ' . $this->cli->color(str_pad($taskName . $actionName, $longestName, ' '), 'green') . $actionInfo['description']);

			$this->cli->newline();

			if(!empty($actionInfo['options']))
			{
				$longestOptionName = max(array_map('strlen', array_keys($actionInfo['options']))) + 5;

				foreach($actionInfo['options'] as $optionName => $optionDescription)
				{
					$this->cli->stdout(str_repeat(' ', $longestName) . $this->cli->color(str_pad(' --' .$optionName, $longestOptionName, ' '), 'blue') . $optionDescription);
				}

				$this->cli->newline();
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
		$this->cli->stderr(vsprintf("Unknown task action '%s'.", array($name)));

		$this->cli->newLine();

		$this->displayTaskInfo();
	}
}

/** -------------------- End of file -------------------- **/