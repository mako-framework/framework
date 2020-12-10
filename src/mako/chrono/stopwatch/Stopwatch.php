<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono\stopwatch;

use function count;
use function end;
use function microtime;

/**
 * Stopwatch.
 */
class Stopwatch
{
	/**
	 * Laps.
	 *
	 * @var array
	 */
	protected $laps = [];

	/**
	 * Returns the laps.
	 *
	 * @return array
	 */
	public function getLaps(): array
	{
		return $this->laps;
	}

	/**
	 * Returns the number of laps.
	 *
	 * @return int
	 */
	public function getLapCount(): int
	{
		return count($this->laps);
	}

	/**
	 * Returns TRUE if the stopwatch is still running and FALSE if not.
	 *
	 * @return bool
	 */
	public function isRunning(): bool
	{
		return end($this->laps)->isRunning();
	}

	/**
	 * Starts the stopwatch.
	 *
	 * @return \mako\chrono\stopwatch\Stopwatch
	 */
	public function start(): Stopwatch
	{
		$this->laps[] = (new Lap)->start();

		return $this;
	}

	/**
	 * Starts a new lap and returns the time of the previous lap.
	 *
	 * @return float
	 */
	public function lap(): float
	{
		$last = end($this->laps);

		$time = $last->stop();

		$this->start();

		return $time;
	}

	/**
	 * Get elapsed time.
	 *
	 * @return float
	 */
	public function getElapsedTime(): float
	{
		$last = end($this->laps);

		return ($last->isRunning() ? microtime(true) : $last->getStopTime()) - $this->laps[0]->getStartTime();
	}

	/**
	 * Stops the timer and returns the elapsed time.
	 *
	 * @return float
	 */
	public function stop(): float
	{
		$last = end($this->laps);

		$last->stop();

		return $last->getStopTime() - $this->laps[0]->getStartTime();
	}
}
