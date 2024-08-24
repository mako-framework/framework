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
	 */
	public const int MINUTE = 60;

	/**
	 * Number of seconds in an hour.
	 */
	public const int HOUR = 3600;

	/**
	 * Number of seconds in a day.
	 */
	public const int DAY = 86400;

	/**
	 * Number of seconds in a week.
	 */
	public const int WEEK = 604800;

	/**
	 * Average number of seconds in a month.
	 */
	public const int MONTH = 2629744;

	/**
	 * Average number of seconds in a year.
	 */
	public const int YEAR = 31556926;

	/**
	 * Returns a new instance set to the current time.
	 */
	public static function now(null|DateTimeZone|string $timeZone = null): static;

	/**
	 * Returns a new instance according to the specified date.
	 */
	public static function createFromDate(int $year, ?int $month = null, ?int $day = null, null|DateTimeZone|string $timeZone = null): static;

	/**
	 * Returns a new instance according to the specified UNIX timestamp.
	 */
	public static function createFromTimestamp(float|int $timestamp, null|DateTimeZone|string $timeZone = null): static;

	/**
	 * Returns a new instance according to the specified DOS timestamp.
	 */
	public static function createFromDOSTimestamp(int $timestamp, null|DateTimeZone|string $timeZone = null): static;

	/**
	 * Returns a new instance according to the specified time string.
	 */
	public static function createFromFormat(string $format, string $time, null|DateTimeZone|string $timeZone = null): static;

	/**
	 * Returns a copy of the current instance.
	 */
	public function copy(): static;

	/**
	 * Sets the time zone.
	 *
	 * @return $this|static
	 */
	public function setTimezone(DateTimeZone|string $timeZone): static;

	/**
	 * Move forward in time by x seconds.
	 *
	 * @return $this|static
	 */
	public function forward(int $seconds): static;

	/**
	 * Move backward in time by x seconds.
	 *
	 * @return $this|static
	 */
	public function rewind(int $seconds): static;

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
