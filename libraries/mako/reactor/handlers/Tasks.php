<?php

namespace mako\reactor\handlers
{
	use \mako\Mako;
	use \mako\CLI;
	use \mako\reactor\Reactor;
	use \ReflectionClass;
	use \RuntimeException;

	/**
	* Task handler.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Tasks
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		// Nothing here

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
		* Runs the chosen task.
		*
		* @access  public
		* @param   array   Arguments
		*/

		public static function run($arguments)
		{
			if(!empty($arguments))
			{
				if(strrpos($arguments[0], '::'))
				{
					list($bundle, $task) = explode('::', $arguments[0]);

					try
					{
						Mako::bundle($bundle);
					}
					catch(RuntimeException $e)
					{
						return CLI::stderr($e->getMessage());
					}
				}
				else
				{
					$task = $arguments[0];
				}

				list($task, $method) = explode(':', $task);

				$file = (!empty($bundle) ? MAKO_BUNDLES . '/' . $bundle : MAKO_APPLICATION ) . '/tasks/' . $task . '.php';

				if(!file_exists($file))
				{
					return CLI::stderr(vsprintf("The '%s' task does not exist", array($task)));
				}

				include $file;

				// Validate task class

				$taskClass = new ReflectionClass($task);

				if($taskClass->isSubClassOf('\mako\reactor\Task') === false)
				{
					return CLI::stderr(vsprintf("The '%s' task needs to extend the mako\\reactor\Task class", array($task)));
				}

				// Run task

				$task = $taskClass->newInstance();

				call_user_func_array(array($task, (empty($method) ? 'run' : $method)), array_slice($arguments, 1));
			}
			else
			{
				Reactor::help();
			}
		}
	}
}

/** -------------------- End of file --------------------**/