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
		if($timeZone !== null && ($timeZone instanceof DateTimeZone) === false)
		{
			$timeZone = new DateTimeZone($timeZone);
		}

		parent::__construct($time, $timeZone);
	}

	/**
	 * Returns a new instance set to the current time.
	 */
	public static function now(null|DateTimeZone|string $timeZone = null): static
	{
		return new static('now', $timeZone);
	}

	/**
	 * Returns a new instance according to the specified date.
	 *
	 * @return false|static
	 */
	public static function createFromDate(int $year, ?int $month = null, ?int $day = null, null|DateTimeZone|string $timeZone = null)
	{
		$date = (clone $now = static::now($timeZone))->setDate($year, 1, 1);

		$month ??= $now->format('n');

		$day ??= min($date->daysInMonths()[$month - 1], $now->format('j'));

		return $date->setDate($year, $month, $day);
	}

	/**
	 * Returns a new instance according to the specified UNIX timestamp.
	 *
	 * @return false|static
	 */
	public static function createFromTimestamp(int $timestamp, null|DateTimeZone|string $timeZone = null)
	{
		return (new static('now', $timeZone))->setTimestamp($timestamp);
	}

	/**
	 * Returns a new instance according to the specified DOS timestamp.
	 *
	 * @return false|static
	 */
	public static function createFromDOSTimestamp(int $timestamp, null|DateTimeZone|string $timeZone = null)
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
	 * @return false|static
	 */
	#[\ReturnTypeWillChange]
	public static function createFromFormat(string $format, string $time, null|DateTimeZone|string $timeZone = null)
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
	 * @return $this|false|static
	 */
	#[\ReturnTypeWillChange]
	public function setTimezone(DateTimeZone|string $timeZone)
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
	 * @return $this|false|static
	 */
	public function forward(int $seconds)
	{
		return $this->setTimestamp($this->getTimestamp() + $seconds);
	}

	/**
	 * Move backward in time by x seconds.
	 *
	 * @return $this|false|static
	 */
	public function rewind(int $seconds)
	{
		return $this->setTimestamp($this->getTimestamp() - $seconds);
	}

	/**
	 * Returns the DOS timestamp.
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
	 */
	public function daysInMonth(): int
	{
		return $this->daysInMonths()[(int) $this->format('n') - 1];
	}
}
