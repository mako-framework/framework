<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono\stopwatch;

use function array_last;
use function count;
use function microtime;

/**
 * Stopwatch.
 */
class Stopwatch
{
	/**
	 * Laps.
	 */
	protected array $laps = [];

	/**
	 * Returns the laps.
	 */
	public function getLaps(): array
	{
		return $this->laps;
	}

	/**
	 * Returns the number of laps.
	 */
	public function getLapCount(): int
	{
		return count($this->laps);
	}

	/**
	 * Returns TRUE if the stopwatch is still running and FALSE if not.
	 */
	public function isRunning(): bool
	{
		return array_last($this->laps)->isRunning();
	}

	/**
	 * Starts the stopwatch.
	 *
	 * @return $this
	 */
	public function start(): static
	{
		$this->laps[] = (new Lap)->start();

		return $this;
	}

	/**
	 * Starts a new lap and returns the time of the previous lap.
	 */
	public function lap(): float
	{
		$last = array_last($this->laps);

		$time = $last->stop();

		$this->start();

		return $time;
	}

	/**
	 * Get elapsed time.
	 */
	public function getElapsedTime(): float
	{
		$last = array_last($this->laps);

		return ($last->isRunning() ? microtime(true) : $last->getStopTime()) - $this->laps[0]->getStartTime();
	}

	/**
	 * Stops the timer and returns the elapsed time.
	 */
	public function stop(): float
	{
		$last = array_last($this->laps);

		$last->stop();

		return $last->getStopTime() - $this->laps[0]->getStartTime();
	}
}
