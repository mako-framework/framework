<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application;

/**
 * Deferred tasks.
 */
class DeferredTasks
{
	/**
	 * Deferred tasks.
	 *
	 * @var array<int, callable>
	 */
	protected array $deferredTasks = [];

	/**
	 * Defer a task.
	 */
	public function defer(callable $task): void
	{
		$this->deferredTasks[] = $task;
	}

	/**
	 * Returns the deferred tasks.
	 *
	 * @return array<int, callable>
	 */
	public function getTasks(): array
	{
		return $this->deferredTasks;
	}
}
