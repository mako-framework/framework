<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeInterface;
use Time\Duration;

/**
 * Sleeper interface.
 */
interface SleeperInterface
{
	/**
	 * Sleep for the given number of seconds.
	 *
	 * @param int<0, max> $seconds
	 */
	public function sleep(int $seconds): void;

	/**
	 * Sleep for the given number of milliseconds.
	 *
	 * @param int<0, max> $milliseconds
	 */
	public function milliSleep(int $milliseconds): void;

	/**
	 * Sleep for the given number of microseconds.
	 *
	 * @param int<0, max> $microseconds
	 */
	public function microSleep(int $microseconds): void;

	/**
	 * Sleep for the given number of nanoseconds.
	 * Note that actual sleep duration depends on OS timer resolution.
	 *
	 * @param int<0, max> $nanoseconds
	 */
	public function nanoSleep(int $nanoseconds): void;

	/**
	 * Sleep until the specified time.
	 */
	public function sleepUntil(DateTimeInterface $dateTime): void;

	/**
	 * Sleep for the specified duration.
	 */
	public function sleepFor(Duration $duration): void;
}
