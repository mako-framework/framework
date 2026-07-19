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
	public static function now(null|DateTimeZone|string $timezone = null): static;

	/**
	 * Returns a new instance set to midnight today.
	 */
	public static function today(null|DateTimeZone|string $timezone = null): static;

	/**
	 * Returns a new instance set to midnight yesterday.
	 */
	public static function yesterday(null|DateTimeZone|string $timezone = null): static;

	/**
	 * Returns a new instance set to midnight tomorrow.
	 */
	public static function tomorrow(null|DateTimeZone|string $timezone = null): static;

	/**
	 * Returns a new instance according to the specified date.
	 */
	public static function createFromDate(int $year, ?int $month = null, ?int $day = null, null|DateTimeZone|string $timezone = null): static;

	/**
	 * Returns a new instance according to the specified UNIX timestamp.
	 */
	public static function createFromTimestamp(float|int $timestamp, null|DateTimeZone|string $timezone = null): static;

	/**
	 * Returns a new instance according to the specified DOS timestamp.
	 */
	public static function createFromDOSTimestamp(int $timestamp, null|DateTimeZone|string $timezone = null): static;

	/**
	 * Returns a new instance according to the specified time string.
	 */
	public static function createFromFormat(string $format, string $datetime, null|DateTimeZone|string $timezone = null): false|static;

	/**
	 * Returns a new instance according to the specified time string.
	 */
	public static function createFromFormatOrThrow(string $format, string $datetime, null|DateTimeZone|string $timezone = null): static;

	/**
	 * Sets the time zone.
	 *
	 * @return $this|static
	 */
	public function setTimezone(DateTimeZone|string $timezone): static;

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
	 * Returns TRUE if the time is in the past and FALSE if not.
	 */
	public function isPast(): bool;

	/**
	 * Returns TRUE if the time is in the future and FALSE if not.
	 */
	public function isFuture(): bool;

	/**
	 * Returns TRUE if the time is before the given instance or FALSE if not.
	 */
	public function isBefore(DateTimeInterface $time): bool;

	/**
	 * Returns TRUE if the time is after the given instance or FALSE if not.
	 */
	public function isAfter(DateTimeInterface $time): bool;

	/**
	 * Returns TRUE if the time is between the two given instances or FALSE it not.
	 */
	public function isBetween(DateTimeInterface $start, DateTimeInterface $end): bool;

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

	/**
	 * Returns the time as an ATOM date-time string.
	 */
	public function toAtomString(): string;

	/**
	 * Returns the time as an ISO 8601 date-time string.
	 */
	public function toIso8601String(): string;

	/**
	 * Returns the time as an expanded ISO 8601 date-time string.
	 */
	public function toExpandedIso8601String(): string;

	/**
	 * Returns the time as an RFC 7231 date-time string.
	 */
	public function toRfc7231String(): string;
}
