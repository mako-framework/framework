<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeImmutable;
use Override;
use Psr\Clock\ClockInterface;

/**
 * Clock implementation that provides access to the system clock.
 */
final class Clock implements ClockInterface
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function now(): DateTimeImmutable
	{
		return new DateTimeImmutable;
	}
}
