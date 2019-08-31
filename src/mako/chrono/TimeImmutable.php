<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeImmutable;
use mako\chrono\traits\TimeTrait;

/**
 * Extension of the PHP DateTimeImmutable class.
 *
 * @author Frederic G. Østby
 *
 * @method static|false setTimezone(string|\DateTimeZone $timeZone)
 * @method static|false forward(int seconds)
 * @method static|false rewind(int seconds)
 */
class TimeImmutable extends DateTimeImmutable implements TimeInterface
{
	use TimeTrait;

	/**
	 * Returns a mutable instance of the current instance.
	 *
	 * @return \mako\chrono\Time
	 */
	public function getMutable(): Time
	{
		return new Time($this->format('Y-m-d H:i:s.u'), $this->getTimezone());
	}
}
