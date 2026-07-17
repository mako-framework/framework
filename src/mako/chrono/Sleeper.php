<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeInterface;
use Override;

use function intdiv;
use function sleep;
use function time_nanosleep;
use function time_sleep_until;
use function usleep;

/**
 * Sleeper that provides time-based delays.
 */
final class Sleeper implements SleeperInterface
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function sleep(int $seconds): void
	{
		sleep($seconds);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function milliSleep(int $milliseconds): void
	{
		usleep($milliseconds * 1000);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function microSleep(int $microseconds): void
	{
		usleep($microseconds);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function nanoSleep(int $nanoseconds): void
	{
		$seconds = intdiv($nanoseconds, 1_000_000_000);
		$remainingNanoseconds = $nanoseconds % 1_000_000_000;

		time_nanosleep($seconds, $remainingNanoseconds);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function sleepUntil(DateTimeInterface $dateTime): void
	{
		time_sleep_until((float) $dateTime->format('U.u'));
	}
}
