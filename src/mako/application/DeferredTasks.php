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
	 * @var callable[]
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
	 * @return callable[]
	 */
	public function getTasks(): array
	{
		return $this->deferredTasks;
	}
}
