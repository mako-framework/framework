<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeInterface;

/**
 * Sleeper interface.
 */
interface SleeperInterface
{
	/**
	 * Sleep for the given number of seconds.
	 */
	public function sleep(int $seconds): void;

	/**
	 * Sleep for the given number of milliseconds.
	 */
	public function milliSleep(int $milliseconds): void;

	/**
	 * Sleep for the given number of microseconds.
	 */
	public function microSleep(int $microseconds): void;

	/**
	 * Sleep for the given number of nanoseconds.
	 * Note that actual sleep duration depends on OS timer resolution.
	 */
	public function nanoSleep(int $nanoseconds): void;

	/**
	 * Sleep until the specified time.
	 */
	public function sleepUntil(DateTimeInterface $dateTime): void;
}
