<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeImmutable;
use DateTimeZone;
use mako\chrono\traits\TimeTrait;
use Override;

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
	 * {@inheritDoc}
	 */
	#[Override]
	public function toRfc7231String(): string
	{
		return $this->setTimezone('UTC')->format(static::RFC_7231_DATE);
	}

	/**
	 * Returns a mutable instance of the current instance.
	 */
	public function toMutable(): Time
	{
		return Time::createFromImmutable($this);
	}

	/**
	 * Returns a native PHP DateTime instance.
	 */
	public function toNative(): DateTimeImmutable
	{
		return DateTimeImmutable::createFromInterface($this);
	}
}
