<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor;

use mako\application\CommandLine;

/**
 * Finds all tasks.
 *
 * @author  Frederic G. Østby
 */

class TaskFinder
{
	/**
	 * Application instance.
	 * 
	 * @var \mako\application\CommandLine
	 */

	protected $application;

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
	 * @param   string  $application  Application instance
	 */

	public function __construct(CommandLine $application)
	{
		$this->application = $application;

		$this->applicationPath = $this->application->getPath();

		$this->fileSystem = $this->application->getContainer()->get('fileSystem');
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
	 * Returns all application tasks.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function findApplicationTasks()
	{
		$tasks = [];

		$namespace = $this->application->getNamespace(true);

		foreach($this->fileSystem->glob($this->applicationPath . '/tasks/*.php') as $task)
		{
			$baseName = $this->getBasename($task);

			$tasks[strtolower($baseName)] = $namespace . '\\tasks\\' . $baseName;
		}

		return $tasks;
	}

	/**
	 * Returns all package tasks.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function findPackageTasks()
	{
		$tasks = [];

		foreach($this->application->getPackages() as $package)
		{
			$namespace = $package->getClassNamespace();

			foreach($this->fileSystem->glob($package->getPath() . '/src/tasks/*.php') as $task)
			{
				$baseName = $this->getBasename($task);

				$tasks[$package->getFileNamespace() . '::' . strtolower($baseName)] = $namespace . '\\tasks\\' . $baseName;
			}
		}

		return $tasks;
	}

	/**
	 * Returns all core tasks.
	 * 
	 * @access  protected
	 * @return  array
	 */

	public function findCoreTasks()
	{
		$tasks = [];

		foreach($this->fileSystem->glob(__DIR__ . '/tasks/*.php') as $task)
		{
			$baseName = $this->getBasename($task);

			$tasks[strtolower($baseName)] = '\mako\reactor\tasks\\'. $baseName;
		}

		return $tasks;
	}

	/**
	 * Returns all tasks.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function find()
	{
		return $this->findApplicationTasks() + $this->findPackageTasks() + $this->findCoreTasks();
	}
}