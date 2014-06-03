<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor;

/**
 * Finds all tasks.
 *
 * @author  Frederic G. Østby
 */

class TaskFinder
{
	use \mako\reactor\HelpersTrait;

	/**
	 * Application path.
	 * 
	 * @var string
	 */

	protected $applicationPath;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $applicationPath  Application path
	 */

	public function __construct($applicationPath)
	{
		$this->applicationPath = $applicationPath;
	}

	/**
	 * Returns task directories.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function getTaskDirs()
	{
		return 
		[
			['path' => $this->applicationPath . '/tasks/*',            'type' => 'app'],
			['path' => $this->applicationPath . '/packages/*/tasks/*', 'type' => 'package'],
			['path' => __DIR__ . '/tasks/*',                           'type' => 'core'],
		];
	}

	/**
	 * Returns the basename of the task.
	 * 
	 * @access  protected
	 * @param   string     $task  Task path
	 * @return  string
	 */

	protected function getBaseName($task)
	{
		return basename($task, '.php');
	}

	/**
	 * Returns the package name of the task.
	 * 
	 * @access  protected
	 * @param   string     $task  Task path
	 * @return  string
	 */

	protected function getPackageName($task)
	{
		preg_match('/packages\/(.*)\/tasks/', $task, $matches);

		return $matches[1];
	}

	/**
	 * Returns app task info.
	 * 
	 * @access  protected
	 * @param   string     $task  Task path
	 * @return  array
	 */

	protected function getAppTask($task)
	{
		$baseName = $this->getBasename($task);

		$namespace = $this->getApplicationNamespace($this->applicationPath, true);

		return [strtolower($baseName) => $namespace . '\\tasks\\' . $baseName];
	}

	/**
	 * Returns package task info.
	 * 
	 * @access  protected
	 * @param   string     $task  Task path
	 * @return  array
	 */

	protected function getPackageTask($task)
	{
		$baseName = $this->getBasename($task);

		$packageName = $this->getPackageName($task);

		return [$packageName . '::' . strtolower($baseName) => '\\' . $packageName . '\tasks\\' . $baseName];
	}

	/**
	 * Returns core task info.
	 * 
	 * @access  protected
	 * @param   string     $task  Task path
	 * @return  array
	 */

	protected function getCoreTask($task)
	{
		$baseName = $this->getBasename($task);

		return ['mako::' . strtolower($baseName) => '\mako\reactor\tasks\\' . $baseName];
	}

	/**
	 * Returns all tasks.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function find()
	{
		$tasks = [];

		foreach($this->getTaskDirs() as $taskDir)
		{
			$found = glob($taskDir['path']);

			foreach($found as $task)
			{
				if(!is_dir($task))
				{
					switch($taskDir['type'])
					{
						case 'app':
							$task = $this->getAppTask($task);
							break;
						case 'package':
							$task = $this->getPackageTask($task);
							break;
						case 'core':
							$task = $this->getCoreTask($task);
							break;
					}

					$tasks = array_merge($tasks, $task);
				}
			}
		}

		return $tasks;
	}
}