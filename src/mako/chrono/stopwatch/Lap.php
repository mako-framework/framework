<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono\stopwatch;

use function microtime;

/**
 * Stopwatch lap.
 *
 * @author Yamada Taro
 */
class Lap
{
	/**
	 * Start time.
	 *
	 * @var float
	 */
	protected $started = 0.0;

	/**
	 * Stop time.
	 *
	 * @var float
	 */
	protected $stopped = 0.0;

	/**
	 * Returns the lap start time.
	 *
	 * @return float
	 */
	public function getStartTime(): float
	{
		return $this->started;
	}

	/**
	 * Returns the lap stop time.
	 *
	 * @return float
	 */
	public function getStopTime(): float
	{
		return $this->stopped;
	}

	/**
	 * Returns TRUE if the lap is still running and FALSE if not.
	 *
	 * @return bool
	 */
	public function isRunning(): bool
	{
		return $this->stopped === 0.0;
	}

	/**
	 * Starts the lap.
	 *
	 * @return \mako\chrono\stopwatch\Lap
	 */
	public function start(): Lap
	{
		$this->started = microtime(true);

		return $this;
	}

	/**
	 * Stops the lap and returns the elapsed time.
	 *
	 * @return float
	 */
	public function stop(): float
	{
		return ($this->stopped = microtime(true)) - $this->started;
	}
}
