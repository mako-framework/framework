<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono\traits;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use mako\chrono\exceptions\ChronoException;
use Override;

use function getdate;
use function implode;
use function min;
use function mktime;
use function sprintf;

/**
 * Time trait.
 *
 * @mixin DateTime|DateTimeImmutable
 */
trait TimeTrait
{
	/**
	 * Constructor.
	 */
	final public function __construct(string $datetime = 'now', null|DateTimeZone|string $timezone = null)
	{
		if ($timezone !== null && ($timezone instanceof DateTimeZone) === false) {
			$timezone = new DateTimeZone($timezone);
		}

		parent::__construct($datetime, $timezone);
	}

	/**
	 * Returns a new instance set to the current time.
	 */
	#[Override]
	public static function now(null|DateTimeZone|string $timezone = null): static
	{
		return new static('now', $timezone);
	}

	/**
	 * Returns a new instance set to midnight today.
	 */
	#[Override]
	public static function today(null|DateTimeZone|string $timezone = null): static
	{
		return new static('midnight', $timezone);
	}

	/**
	 * Returns a new instance set to midnight yesterday.
	 */
	#[Override]
	public static function yesterday(null|DateTimeZone|string $timezone = null): static
	{
		return new static('yesterday, midnight', $timezone);
	}

	/**
	 * Returns a new instance set to midnight tomorrow.
	 */
	#[Override]
	public static function tomorrow(null|DateTimeZone|string $timezone = null): static
	{
		return new static('tomorrow, midnight', $timezone);
	}

	/**
	 * Returns a new instance according to the specified date.
	 */
	#[Override]
	public static function createFromDate(?int $year = null, ?int $month = null, ?int $day = null, null|DateTimeZone|string $timezone = null): static
	{
		$now = new static('midnight', $timezone);

		$date = (clone $now)->setDate($year ?? (int) $now->format('Y'), 1, 1);

		$month ??= (int) $now->format('n');

		$day ??= min($date->daysInMonths()[$month - 1], (int) $now->format('j'));

		return $date->setDate($year, $month, $day);
	}

	/**
	 * Returns a new instance according to the specified UNIX timestamp.
	 */
	#[Override]
	public static function createFromTimestamp(float|int $timestamp, null|DateTimeZone|string $timezone = null): static
	{
		$time = static::createFromInterface(parent::createFromTimestamp($timestamp));

		return $timezone == null ? $time : $time->setTimezone($timezone);
	}

	/**
	 * Returns a new instance according to the specified DOS timestamp.
	 */
	#[Override]
	public static function createFromDOSTimestamp(int $timestamp, null|DateTimeZone|string $timezone = null): static
	{
		$year     = (($timestamp >> 25) & 0x7F) + 1980;
		$mon      = ($timestamp >> 21) & 0x0F;
		$mday     = ($timestamp >> 16) & 0x1F;
		$hours    = ($timestamp >> 11) & 0x1F;
		$minutes  = ($timestamp >> 5) & 0x3F;
		$seconds  = 2 * ($timestamp & 0x1F);

		$timestamp = mktime($hours, $minutes, $seconds, $mon, $mday, $year);

		return static::createFromTimestamp($timestamp, $timezone);
	}

	/**
	 * Returns a new instance according to the specified time string.
	 */
	#[Override]
	public static function createFromFormat(string $format, string $datetime, null|DateTimeZone|string $timezone = null): false|static
	{
		if ($timezone !== null && ($timezone instanceof DateTimeZone) === false) {
			$timezone = new DateTimeZone($timezone);
		}

		$date = parent::createFromFormat($format, $datetime, $timezone);

		if ($date === false) {
			return false;
		}

		return static::createFromInterface($date);
	}

	/**
	 * Returns a new instance according to the specified time string.
	 */
	#[Override]
	public static function createFromFormatOrThrow(string $format, string $datetime, null|DateTimeZone|string $timezone = null): static
	{
		$date = static::createFromFormat($format, $datetime, $timezone);

		if ($date === false) {
			$errors = static::getLastErrors();

			$message = $errors === false ? 'Unknown error' : implode('; ', $errors['errors']);

			throw new ChronoException(sprintf(
				'Unable to create %s instance from value [ %s ] for format [ %s ]. %s.',
				static::class,
				$datetime,
				$format,
				$message
			));
		}

		return $date;
	}

	/**
	 * Sets the time zone.
	 */
	#[Override]
	public function setTimezone(DateTimeZone|string $timezone): static
	{
		if (($timezone instanceof DateTimeZone) === false) {
			$timezone = new DateTimeZone($timezone);
		}

		return parent::setTimezone($timezone);
	}

	/**
	 * Move forward in time by x seconds.
	 *
	 * @return $this|static
	 */
	#[Override]
	public function forward(int $seconds): static
	{
		return $this->setTimestamp($this->getTimestamp() + $seconds);
	}

	/**
	 * Move backward in time by x seconds.
	 *
	 * @return $this|static
	 */
	#[Override]
	public function rewind(int $seconds): static
	{
		return $this->setTimestamp($this->getTimestamp() - $seconds);
	}

	/**
	 * Returns the DOS timestamp.
	 */
	#[Override]
	public function getDOSTimestamp(): int
	{
		$time = getdate($this->getTimestamp());

		if ($time['year'] < 1980) {
			$time['year']    = 1980;
			$time['mon']     = 1;
			$time['mday']    = 1;
			$time['hours']   = 0;
			$time['minutes'] = 0;
			$time['seconds'] = 0;
		}

		return (($time['year'] - 1980) << 25) | ($time['mon'] << 21) | ($time['mday'] << 16) | ($time['hours'] << 11) | ($time['minutes'] << 5) | ($time['seconds'] >> 1);
	}

	/**
	 * Returns TRUE if the time is in the past and FALSE if not.
	 */
	#[Override]
	public function isPast(): bool
	{
		return $this < new static;
	}

	/**
	 * Returns TRUE if the time is in the future and FALSE if not.
	 */
	#[Override]
	public function isFuture(): bool
	{
		return $this > new static;
	}

	/**
	 * Returns TRUE if the year is a leap year and FALSE if not.
	 */
	#[Override]
	public function isLeapYear(): bool
	{
		return $this->format('L') === '1';
	}

	/**
	 * Returns an array containing the number of days in each month of the year.
	 */
	#[Override]
	public function daysInMonths(): array
	{
		return [
			31,
			$this->isLeapYear() ? 29 : 28,
			31,
			30,
			31,
			30,
			31,
			31,
			30,
			31,
			30,
			31,
		];
	}

	/**
	 * Returns the number of days in the current or specified month.
	 */
	#[Override]
	public function daysInMonth(): int
	{
		return (int) $this->format('t');
	}
}
