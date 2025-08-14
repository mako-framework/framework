<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeImmutable;
use mako\chrono\traits\TimeTrait;
use Override;

/**
 * Extension of the PHP DateTimeImmutable class.
 *
 * @method static setTimezone(\DateTimeZone|string $timeZone)
 * @method static forward(int $seconds)
 * @method static rewind(int $seconds)
 */
class TimeImmutable extends DateTimeImmutable implements TimeInterface
{
	use TimeTrait;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function copy(): static
	{
		return $this; // No need to clone the object as it's immutable
	}

	/**
	 * Returns a mutable instance of the current instance.
	 */
	public function getMutable(): Time
	{
		return new Time($this->format('Y-m-d H:i:s.u'), $this->getTimezone());
	}
}
