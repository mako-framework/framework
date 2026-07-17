<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeImmutable;
use DateTimeZone;
use Override;
use Psr\Clock\ClockInterface;

/**
 * Clock implementation that provides access to the system clock.
 */
final class Clock implements ClockInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		private null|DateTimeZone|string $timezone = null
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return TimeImmutable
	 */
	#[Override]
	public function now(): DateTimeImmutable
	{
		return TimeImmutable::now($this->timezone);
	}
}
