<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeInterface;
use DateTimeZone;

/**
 * Time interface.
 */
interface TimeInterface extends DateTimeInterface
{
	/**
	 * Number of seconds in a minute.
	 *
	 * @var int
	 */
	public const MINUTE = 60;

	/**
	 * Number of seconds in an hour.
	 *
	 * @var int
	 */
	public const HOUR = 3600;

	/**
	 * Number of seconds in a day.
	 *
	 * @var int
	 */
	public const DAY = 86400;

	/**
	 * Number of seconds in a week.
	 *
	 * @var int
	 */
	public const WEEK = 604800;

	/**
	 * Average number of seconds in a month.
	 *
	 * @var int
	 */
	public const MONTH = 2629744;

	/**
	 * Average number of seconds in a year.
	 *
	 * @var int
	 */
	public const YEAR = 31556926;

	/**
	 * Returns a new instance set to the current time.
	 */
	public static function now(null|DateTimeZone|string $timeZone = null): static;

	/**
	 * Returns a new instance according to the specified date.
	 *
	 * @return false|static
	 */
	public static function createFromDate(int $year, ?int $month = null, ?int $day = null, null|DateTimeZone|string $timeZone = null);

	/**
	 * Returns a new instance according to the specified UNIX timestamp.
	 *
	 * @return false|static
	 */
	public static function createFromTimestamp(int $timestamp, null|DateTimeZone|string $timeZone = null);

	/**
	 * Returns a new instance according to the specified DOS timestamp.
	 *
	 * @return false|static
	 */
	public static function createFromDOSTimestamp(int $timestamp, null|DateTimeZone|string $timeZone = null);

	/**
	 * Returns a new instance according to the specified time string.
	 *
	 * @return false|static
	 */
	public static function createFromFormat(string $format, string $time, null|DateTimeZone|string $timeZone = null);

	/**
	 * Returns a copy of the current instance.
	 *
	 * @return $this
	 */
	public function copy();

	/**
	 * Sets the time zone.
	 *
	 * @return $this|false|static
	 */
	public function setTimezone(DateTimeZone|string $timeZone);

	/**
	 * Move forward in time by x seconds.
	 *
	 * @return $this|false|static
	 */
	public function forward(int $seconds);

	/**
	 * Move backward in time by x seconds.
	 *
	 * @return $this|false|static
	 */
	public function rewind(int $seconds);

	/**
	 * Returns the DOS timestamp.
	 */
	public function getDOSTimestamp(): int;

	/**
	 * Returns TRUE if the year is a leap year and FALSE if not.
	 */
	public function isLeapYear(): bool;

	/**
	 * Returns an array containing the number of days in each month of the year.
	 */
	public function daysInMonths(): array;

	/**
	 * Returns the number of days in the current or specified month.
	 */
	public function daysInMonth(): int;
}
