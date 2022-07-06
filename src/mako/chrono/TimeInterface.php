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
	 *
	 * @param  \DateTimeZone|string|null $timeZone A valid time zone or a DateTimeZone object
	 * @return static
	 */
	public static function now(DateTimeZone|string|null $timeZone = null): static;

	/**
	 * Returns a new instance according to the specified date.
	 *
	 * @param  int                       $year     Year
	 * @param  int|null                  $month    Month (1 to 12)
	 * @param  int|null                  $day      Day of month (1 to 31)
	 * @param  \DateTimeZone|string|null $timeZone A valid time zone or a DateTimeZone object
	 * @return false|static
	 */
	public static function createFromDate(int $year, ?int $month = null, ?int $day = null, DateTimeZone|string|null $timeZone = null);

	/**
	 * Returns a new instance according to the specified UNIX timestamp.
	 *
	 * @param  int                       $timestamp UNIX timestamp
	 * @param  \DateTimeZone|string|null $timeZone  A valid time zone or a DateTimeZone object
	 * @return false|static
	 */
	public static function createFromTimestamp(int $timestamp, DateTimeZone|string|null $timeZone = null);

	/**
	 * Returns a new instance according to the specified DOS timestamp.
	 *
	 * @param  int                       $timestamp DOS timestamp
	 * @param  \DateTimeZone|string|null $timeZone  A valid time zone or a DateTimeZone object
	 * @return false|static
	 */
	public static function createFromDOSTimestamp(int $timestamp, DateTimeZone|string|null $timeZone = null);

	/**
	 * Returns a new instance according to the specified time string.
	 *
	 * @param  string                    $format   The format that the passed in string should be in
	 * @param  string                    $time     String representing the time
	 * @param  \DateTimeZone|string|null $timeZone A valid time zone or a DateTimeZone object
	 * @return false|static
	 */
	public static function createFromFormat(string $format, string $time, DateTimeZone|string|null $timeZone = null);

	/**
	 * Returns a copy of the current instance.
	 *
	 * @return $this
	 */
	public function copy();

	/**
	 * Sets the time zone.
	 *
	 * @param  \DateTimeZone|string $timeZone A valid time zone or a DateTimeZone object
	 * @return $this|false|static
	 */
	public function setTimezone(DateTimeZone|string $timeZone);

	/**
	 * Move forward in time by x seconds.
	 *
	 * @param  int                $seconds Number of seconds
	 * @return $this|false|static
	 */
	public function forward(int $seconds);

	/**
	 * Move backward in time by x seconds.
	 *
	 * @param  int                $seconds Number of seconds
	 * @return $this|false|static
	 */
	public function rewind(int $seconds);

	/**
	 * Returns the DOS timestamp.
	 *
	 * @return int
	 */
	public function getDOSTimestamp(): int;

	/**
	 * Returns TRUE if the year is a leap year and FALSE if not.
	 *
	 * @return bool
	 */
	public function isLeapYear(): bool;

	/**
	 * Returns an array containing the number of days in each month of the year.
	 *
	 * @return array
	 */
	public function daysInMonths(): array;

	/**
	 * Returns the number of days in the current or specified month.
	 *
	 * @return int
	 */
	public function daysInMonth(): int;

	/**
	 * Returns a formatted date string according to current locale settings.
	 *
	 * @param  string $format Date format
	 * @return string
	 */
	public function formatLocalized(string $format): string;
}
