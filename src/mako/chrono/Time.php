<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTime;
use DateTimeZone;
use mako\chrono\traits\TimeTrait;
use Override;

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
	 * {@inheritDoc}
	 */
	#[Override]
	public function toRfc7231String(): string
	{
		return (clone $this)->setTimezone('UTC')->format(static::RFC_7231_DATE);
	}

	/**
	 * Returns an immutable instance of the current instance.
	 */
	public function toImmutable(): TimeImmutable
	{
		return TimeImmutable::createFromMutable($this);
	}

	/**
	 * Returns a native PHP DateTime instance.
	 */
	public function toNative(): DateTime
	{
		return DateTime::createFromInterface($this);
	}
}
