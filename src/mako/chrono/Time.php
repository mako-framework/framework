<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTime;
use DateTimeZone;
use mako\chrono\traits\TimeTrait;

/**
 * Extension of the PHP DateTime class.
 *
 * @method $this setTimezone(DateTimeZone|string $timezone)
 * @method $this forward(int $seconds)
 * @method $this rewind(int $seconds)
 */
class Time extends DateTime implements TimeInterface
{
	use TimeTrait;

	/**
	 * Returns an immutable instance of the current instance.
	 */
	public function getImmutable(): TimeImmutable
	{
		return TimeImmutable::createFromInterface($this);
	}
}
