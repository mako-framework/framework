<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\chrono\stopwatch;

use mako\chrono\stopwatch\Lap;

/**
 * Stopwatch.
 *
 * @author  Yamada Taro
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
	 * @access  public
	 * @return  array
	 */

	public function getLaps()
	{
		return $this->laps;
	}

	/**
	 * Returns the number of laps.
	 *
	 * @access  public
	 * @return  int
	 */

	public function getLapCount()
	{
		return count($this->laps);
	}

	/**
	 * Returns TRUE if the stopwatch is still running and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isRunning()
	{
		return end($this->laps)->isRunning();
	}

	/**
	 * Starts the stopwatch.
	 *
	 * @access  public
	 * @return  \mako\chrono\stopwatch\Stopwatch
	 */

	public function start()
	{
		$this->laps[] = (new Lap)->start();

		return $this;
	}

	/**
	 * Starts a new lap and returns the time of the previous lap.
	 *
	 * @access  public
	 * @return  float
	 */

	public function lap()
	{
		$last = end($this->laps);

		$time = $last->stop();

		$this->start();

		return $time;
	}

	/**
	 * Get elapsed time.
	 *
	 * @access  public
	 * @return  float
	 */

	public function getElapsedTime()
	{
		$last = end($this->laps);

		return ($last->isRunning() ? microtime(true) : $last->getStopTime()) - $this->laps[0]->getStartTime();
	}

	/**
	 * Stops the timer and returns the elapsed time.
	 *
	 * @access  public
	 * @return  float
	 */

	public function stop()
	{
		$last = end($this->laps);

		$last->stop();

		return $last->getStopTime() - $this->laps[0]->getStartTime();
	}
}