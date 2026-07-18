<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeImmutable;
use DateTimeZone;
use mako\chrono\traits\TimeTrait;

/**
 * Extension of the PHP DateTimeImmutable class.
 *
 * @method static setTimezone(DateTimeZone|string $timezone)
 * @method static forward(int $seconds)
 * @method static rewind(int $seconds)
 */
class TimeImmutable extends DateTimeImmutable implements TimeInterface
{
	use TimeTrait;

	/**
	 * Returns a mutable instance of the current instance.
	 */
	public function getMutable(): Time
	{
		return Time::createFromInterface($this);
	}
}
