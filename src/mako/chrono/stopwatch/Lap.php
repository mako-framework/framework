<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\chrono\stopwatch;

/**
 * Stopwatch lap.
 *
 * @author  Yamada Taro
 */

class Lap
{
	/**
	 * Start time.
	 *
	 * @var float
	 */

	protected $started;

	/**
	 * Stop time.
	 *
	 * @var float
	 */

	protected $stopped;

	/**
	 * Returns the lap start time.
	 *
	 * @access  public
	 * @return  float
	 */

	public function getStartTime()
	{
		return $this->started;
	}

	/**
	 * Returns the lap stop time.
	 *
	 * @access  public
	 * @return  null|float
	 */

	public function getStopTime()
	{
		return $this->stopped;
	}

	/**
	 * Returns TRUE if the lap is still running and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isRunning()
	{
		return $this->stopped === null;
	}

	/**
	 * Starts the lap.
	 *
	 * @access  public
	 * @return  \mako\chrono\stopwatch\Lap
	 */

	public function start()
	{
		$this->started = microtime(true);

		return $this;
	}

	/**
	 * Stops the lap and returns the elapsed time.
	 *
	 * @access  public
	 * @return  float
	 */

	public function stop()
	{
		return ($this->stopped = microtime(true)) - $this->started;
	}
}