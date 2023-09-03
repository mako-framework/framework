<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTime;
use mako\chrono\traits\TimeTrait;

/**
 * Extension of the PHP DateTime class.
 *
 * @method $this|false setTimezone(\DateTimeZone|string $timeZone)
 * @method $this|false forward(int $seconds)
 * @method $this|false rewind(int $seconds)
 */
class Time extends DateTime implements TimeInterface
{
	use TimeTrait;

	/**
	 * {@inheritDoc}
	 */
	public function copy()
	{
		return clone $this;
	}

	/**
	 * Returns an immutable instance of the current instance.
	 */
	public function getImmutable(): TimeImmutable
	{
		return new TimeImmutable($this->format('Y-m-d H:i:s.u'), $this->getTimezone());
	}
}
