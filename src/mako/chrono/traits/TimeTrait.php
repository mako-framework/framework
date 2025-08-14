<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono\traits;

use DateTimeZone;
use Override;

use function getdate;
use function min;
use function mktime;

/**
 * Time trait.
 */
trait TimeTrait
{
	/**
	 * Constructor.
	 */
	final public function __construct(string $time = 'now', null|DateTimeZone|string $timeZone = null)
	{
		if ($timeZone !== null && ($timeZone instanceof DateTimeZone) === false) {
			$timeZone = new DateTimeZone($timeZone);
		}

		parent::__construct($time, $timeZone);
	}

	/**
	 * Returns a new instance set to the current time.
	 */
	#[Override]
	public static function now(null|DateTimeZone|string $timeZone = null): static
	{
		return new static('now', $timeZone);
	}

	/**
	 * Returns a new instance according to the specified date.
	 */
	#[Override]
	public static function createFromDate(int $year, ?int $month = null, ?int $day = null, null|DateTimeZone|string $timeZone = null): static
	{
		$date = (clone $now = static::now($timeZone))->setDate($year, 1, 1);

		$month ??= $now->format('n');

		$day ??= min($date->daysInMonths()[$month - 1], $now->format('j'));

		return $date->setDate($year, $month, $day);
	}

	/**
	 * Returns a new instance according to the specified UNIX timestamp.
	 */
	#[Override]
	public static function createFromTimestamp(float|int $timestamp, null|DateTimeZone|string $timeZone = null): static
	{
		return new static(parent::createFromTimestamp($timestamp)->format('Y-m-d\TH:i:s.u'), $timeZone);
	}

	/**
	 * Returns a new instance according to the specified DOS timestamp.
	 */
	#[Override]
	public static function createFromDOSTimestamp(int $timestamp, null|DateTimeZone|string $timeZone = null): static
	{
		$year     = (($timestamp >> 25) & 0x7F) + 1980;
		$mon      = ($timestamp >> 21) & 0x0F;
		$mday     = ($timestamp >> 16) & 0x1F;
		$hours    = ($timestamp >> 11) & 0x1F;
		$minutes  = ($timestamp >> 5) & 0x3F;
		$seconds  = 2 * ($timestamp & 0x1F);

		$timestamp = mktime($hours, $minutes, $seconds, $mon, $mday, $year);

		return static::createFromTimestamp($timestamp, $timeZone);
	}

	/**
	 * Returns a new instance according to the specified time string.
	 */
	#[Override]
	public static function createFromFormat(string $format, string $time, null|DateTimeZone|string $timeZone = null): static
	{
		return new static(parent::createFromFormat($format, $time)->format('Y-m-d\TH:i:s.u'), $timeZone);
	}

	/**
	 * Sets the time zone.
	 */
	#[Override]
	public function setTimezone(DateTimeZone|string $timeZone): static
	{
		if (($timeZone instanceof DateTimeZone) === false) {
			$timeZone = new DateTimeZone($timeZone);
		}

		return parent::setTimezone($timeZone);
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
	 * Returns TRUE if the year is a leap year and FALSE if not.
	 */
	#[Override]
	public function isLeapYear(): bool
	{
		$year = $this->format('Y');

		if ($year % 400 === 0 || ($year % 4 === 0 && $year % 100 !== 0)) {
			return true;
		}

		return false;
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
		return $this->daysInMonths()[(int) $this->format('n') - 1];
	}
}
