<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono\traits;

use DateTimeZone;

use function getdate;
use function min;
use function mktime;
use function strftime;

/**
 * Time trait.
 *
 * @author Frederic G. Østby
 */
trait TimeTrait
{
	/**
	 * Constructor.
	 *
	 * @param string                    $time     A date/time string
	 * @param string|\DateTimeZone|null $timeZone A valid time zone or a DateTimeZone object
	 */
	public function __construct(string $time = 'now', $timeZone = null)
	{
		if($timeZone !== null && ($timeZone instanceof DateTimeZone) === false)
		{
			$timeZone = new DateTimeZone($timeZone);
		}

		parent::__construct($time, $timeZone);
	}

	/**
	 * Returns a new instance set to the current time.
	 *
	 * @param  string|\DateTimeZone|null $timeZone A valid time zone or a DateTimeZone object
	 * @return static
	 */
	public static function now($timeZone = null)
	{
		return new static('now', $timeZone);
	}

	/**
	 * Returns a new instance according to the specified date.
	 *
	 * @param  int                       $year     Year
	 * @param  int|null                  $month    Month (1 to 12)
	 * @param  int|null                  $day      Day of month (1 to 31)
	 * @param  string|\DateTimeZone|null $timeZone A valid time zone or a DateTimeZone object
	 * @return static|false
	 */
	public static function createFromDate(int $year, ?int $month = null, ?int $day = null, $timeZone = null)
	{
		$date = (clone $now = static::now($timeZone))->setDate($year, 1, 1);

		$month = $month ?? $now->format('n');

		$day = $day ?? min($date->daysInMonths()[$month - 1], $now->format('j'));

		return $date->setDate($year, $month, $day);
	}

	/**
	 * Returns a new instance according to the specified UNIX timestamp.
	 *
	 * @param  int                       $timestamp UNIX timestamp
	 * @param  string|\DateTimeZone|null $timeZone  A valid time zone or a DateTimeZone object
	 * @return static|false
	 */
	public static function createFromTimestamp(int $timestamp, $timeZone = null)
	{
		return (new static('now', $timeZone))->setTimestamp($timestamp);
	}

	/**
	 * Returns a new instance according to the specified DOS timestamp.
	 *
	 * @param  int                       $timestamp DOS timestamp
	 * @param  string|\DateTimeZone|null $timeZone  A valid time zone or a DateTimeZone object
	 * @return static|false
	 */
	public static function createFromDOSTimestamp(int $timestamp, $timeZone = null)
	{
		$year     = (($timestamp >> 25) & 0x7f) + 1980;
		$mon      = ($timestamp >> 21) & 0x0f;
		$mday     = ($timestamp >> 16) & 0x1f;
		$hours    = ($timestamp >> 11) & 0x1f;
		$minutes  = ($timestamp >> 5) & 0x3f;
		$seconds  = 2 * ($timestamp & 0x1f);

		$timestamp = mktime($hours, $minutes, $seconds, $mon, $mday, $year);

		return static::createFromTimestamp($timestamp, $timeZone);
	}

	/**
	 * Returns a new instance according to the specified time string.
	 *
	 * @param  string                    $format   The format that the passed in string should be in
	 * @param  string                    $time     String representing the time
	 * @param  string|\DateTimeZone|null $timeZone A valid time zone or a DateTimeZone object
	 * @return static|false
	 */
	#[\ReturnTypeWillChange]
	public static function createFromFormat($format, $time, $timeZone = null)
	{
		if($timeZone !== null)
		{
			if(($timeZone instanceof DateTimeZone) === false)
			{
				$timeZone = new DateTimeZone($timeZone);
			}

			$dateTime = parent::createFromFormat($format, $time, $timeZone);
		}
		else
		{
			$dateTime = parent::createFromFormat($format, $time);
		}

		return new static($dateTime->format('Y-m-d\TH:i:s'), $dateTime->getTimeZone());
	}

	/**
	 * Sets the time zone.
	 *
	 * @param  string|\DateTimeZone $timeZone A valid time zone or a DateTimeZone object
	 * @return $this|static|false
	 */
	#[\ReturnTypeWillChange]
	public function setTimezone($timeZone)
	{
		if(($timeZone instanceof DateTimeZone) === false)
		{
			$timeZone = new DateTimeZone($timeZone);
		}

		return parent::setTimezone($timeZone);
	}

	/**
	 * Move forward in time by x seconds.
	 *
	 * @param  int                $seconds Number of seconds
	 * @return $this|static|false
	 */
	public function forward(int $seconds)
	{
		return $this->setTimestamp($this->getTimestamp() + $seconds);
	}

	/**
	 * Move backward in time by x seconds.
	 *
	 * @param  int                $seconds Number of seconds
	 * @return $this|static|false
	 */
	public function rewind(int $seconds)
	{
		return $this->setTimestamp($this->getTimestamp() - $seconds);
	}

	/**
	 * Returns the DOS timestamp.
	 *
	 * @return int
	 */
	public function getDOSTimestamp(): int
	{
		$time = getdate($this->getTimestamp());

		if($time['year'] < 1980)
		{
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
	 *
	 * @return bool
	 */
	public function isLeapYear(): bool
	{
		$year = $this->format('Y');

		if($year % 400 === 0 || ($year % 4 === 0 && $year % 100 !== 0))
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns an array containing the number of days in each month of the year.
	 *
	 * @return array
	 */
	public function daysInMonths(): array
	{
		return
		[
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
	 *
	 * @return int
	 */
	public function daysInMonth(): int
	{
		return $this->daysInMonths()[$this->format('n') - 1];
	}

	/**
	 * Returns a formatted date string according to current locale settings.
	 *
	 * @param  string $format Date format
	 * @return string
	 */
	public function formatLocalized(string $format): string
	{
		return strftime($format, $this->getTimestamp());
	}
}
