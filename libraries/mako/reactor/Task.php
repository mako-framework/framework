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

		if($this->cli->param('list-actions', false))
		{
			$this->listActions(null, null);
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Run method must always be included.
	 *
	 * @access  public
	 */

	abstract public function run();

	/**
	 * Display list of available actions.
	 *
	 * @access  protected
	 */

	protected function listActions()
	{
		$reflectionClass = new ReflectionClass($this);

		$actions = array();
		$methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

		if(!empty($methods))
		{
			foreach($methods as $method)
			{
				if(!in_array($method->name, array('__construct', '__destruct', '__call')))
				{
					$action = strtolower($reflectionClass->getShortName()) . ($method->name === 'run' ? '' : '.' . $method->name);

					foreach($method->getParameters() as $parameter)
					{
						$parameterName = String::camel2underscored($parameter->getName());

						$action .= $parameter->isOptional() ? ' [<' . $parameterName . '>]' : ' <' . $parameterName . '>';
					}

					$actions[] = $action;
				}
			}

			if(!empty($actions))
			{
				$this->cli->stdout('The available actions for the ' . $this->cli->style($this->cli->color(strtolower($reflectionClass->getShortName()), 'green'), array('underlined')) . ' task are:' . PHP_EOL);

				sort($actions);

				foreach($actions as $action)
				{
					$this->cli->stdout(str_repeat(' ', 4) . $this->cli->color('*', 'yellow') . ' ' . $action);
				}
			}
		}

		exit(PHP_EOL);
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
		$this->cli->stderr(vsprintf("Unknown task action '%s'." . PHP_EOL, array($name)));

		$this->listActions();
	}
}

/** -------------------- End of file --------------------**/