<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeInterface;

/**
 * Time interface.
 *
 * @author Frederic G. Østby
 */
interface TimeInterface extends DateTimeInterface
{
	/**
	 * Number of seconds in a minute.
	 *
	 * @var int
	 */
	const MINUTE = 60;

	/**
	 * Number of seconds in an hour.
	 *
	 * @var int
	 */
	const HOUR = 3600;

	/**
	 * Number of seconds in a day.
	 *
	 * @var int
	 */
	const DAY = 86400;

	/**
	 * Number of seconds in a week.
	 *
	 * @var int
	 */
	const WEEK = 604800;

	/**
	 * Average number of seconds in a month.
	 *
	 * @var int
	 */
	const MONTH = 2629744;

	/**
	 * Average number of seconds in a year.
	 *
	 * @var int
	 */
	const YEAR = 31556926;

	/**
	 * Returns a new instance set to the current time.
	 *
	 * @param  string|\DateTimeZone|null  $timeZone A valid time zone or a DateTimeZone object
	 * @return \mako\chrono\TimeInterface
	 */
	public static function now($timeZone = null): TimeInterface;

	/**
	 * Returns a new instance according to the specified date.
	 *
	 * @param  int                        $year     Year
	 * @param  int|null                   $month    Month (1 to 12)
	 * @param  int|null                   $day      Day of month (1 to 31)
	 * @param  string|\DateTimeZone|null  $timeZone A valid time zone or a DateTimeZone object
	 * @return \mako\chrono\TimeInterface
	 */
	public static function createFromDate(int $year, ?int $month = null, ?int $day = null, $timeZone = null): TimeInterface;

	/**
	 * Returns a new instance according to the specified UNIX timestamp.
	 *
	 * @param  int                        $timestamp UNIX timestamp
	 * @param  string|\DateTimeZone|null  $timeZone  A valid time zone or a DateTimeZone object
	 * @return \mako\chrono\TimeInterface
	 */
	public static function createFromTimestamp(int $timestamp, $timeZone = null): TimeInterface;

	/**
	 * Returns a new instance according to the specified DOS timestamp.
	 *
	 * @param  int                        $timestamp DOS timestamp
	 * @param  string|\DateTimeZone|null  $timeZone  A valid time zone or a DateTimeZone object
	 * @return \mako\chrono\TimeInterface
	 */
	public static function createFromDOSTimestamp(int $timestamp, $timeZone = null): TimeInterface;

	/**
	 * Returns a new instance according to the specified time string.
	 *
	 * @param  string                     $format   The format that the passed in string should be in
	 * @param  string                     $time     String representing the time
	 * @param  string|\DateTimeZone|null  $timeZone A valid time zone or a DateTimeZone object
	 * @return \mako\chrono\TimeInterface
	 */
	public static function createFromFormat($format, $time, $timeZone = null): TimeInterface;

	/**
	 * Sets the time zone.
	 *
	 * @param  string|\DateTimeZone       $timeZone A valid time zone or a DateTimeZone object
	 * @return \mako\chrono\TimeInterface
	 */
	public function setTimezone($timeZone): TimeInterface;

	/**
	 * Move forward in time by x seconds.
	 *
	 * @param  int                        $seconds Number of seconds
	 * @return \mako\chrono\TimeInterface
	 */
	public function forward(int $seconds): TimeInterface;

	/**
	 * Move backward in time by x seconds.
	 *
	 * @param  int                        $seconds Number of seconds
	 * @return \mako\chrono\TimeInterface
	 */
	public function rewind(int $seconds): TimeInterface;

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
