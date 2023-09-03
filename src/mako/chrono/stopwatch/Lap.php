<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono\stopwatch;

use function microtime;

/**
 * Stopwatch lap.
 */
class Lap
{
	/**
	 * Start time.
	 */
	protected float $started = 0.0;

	/**
	 * Stop time.
	 */
	protected float $stopped = 0.0;

	/**
	 * Returns the lap start time.
	 */
	public function getStartTime(): float
	{
		return $this->started;
	}

	/**
	 * Returns the lap stop time.
	 */
	public function getStopTime(): float
	{
		return $this->stopped;
	}

	/**
	 * Returns TRUE if the lap is still running and FALSE if not.
	 */
	public function isRunning(): bool
	{
		return $this->stopped === 0.0;
	}

	/**
	 * Starts the lap.
	 *
	 * @return $this
	 */
	public function start(): static
	{
		$this->started = microtime(true);

		return $this;
	}

	/**
	 * Stops the lap and returns the elapsed time.
	 */
	public function stop(): float
	{
		return ($this->stopped = microtime(true)) - $this->started;
	}
}
