<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTime;
use DateTimeZone;

/**
 * Extension of the PHP DateTime class.
 *
 * @author  Frederic G. Østby
 */

class Time extends DateTime
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
	 * Constructor.
	 *
	 * @access  public
	 * @param   string                     $time      A date/time string
	 * @param   null|string|\DateTimeZone  $timeZone  A valid time zone or a DateTimeZone object
	 */

	public function __construct($time = 'now', $timeZone = null)
	{
		if($timeZone !== null && ($timeZone instanceof DateTimeZone) === false)
		{
			$timeZone = new DateTimeZone($timeZone);
		}

		parent::__construct($time, $timeZone);
	}

	/**
	 * Returns a new Time object.
	 *
	 * @access  public
	 * @param   null|string|\DateTimeZone  $timeZone  A valid time zone or a DateTimeZone object
	 * @return  \mako\chrono\Time
	 */

	public static function now($timeZone = null)
	{
		return new static('now', $timeZone);
	}

	/**
	 * Returns new Time object according to the specified date.
	 *
	 * @access  public
	 * @param   int                        $year      Year
	 * @param   int                        $month     Month (1 to 12)
	 * @param   int                        $day       Day of month (1 to 31)
	 * @param   null|string|\DateTimeZone  $timeZone  A valid time zone or a DateTimeZone object
	 * @return  \mako\chrono\Time
	 */

	public static function createFromDate($year, $month = null, $day = null, $timeZone = null)
	{
		$now = static::now($timeZone);

		return $now->setDate($year, $month ?: $now->format('m'), $day ?: $now->format('d'));
	}

	/**
	 * Returns new Time object according to the specified timestamp.
	 *
	 * @access  public
	 * @param   int                        $timestamp  Unix timestamp
	 * @param   null|string|\DateTimeZone  $timeZone   A valid time zone or a DateTimeZone object
	 * @return  \mako\chrono\Time
	 */

	public static function createFromTimestamp($timestamp, $timeZone = null)
	{
		$dateTime = new static('now', $timeZone);

		$dateTime->setTimestamp($timestamp);

		return $dateTime;
	}

	/**
	 * Returns new Time object according to the specified DOS timestamp.
	 *
	 * @access  public
	 * @param   int                        $timestamp  DOS timestamp
	 * @param   null|string|\DateTimeZone  $timeZone   A valid time zone or a DateTimeZone object
	 * @return  \mako\chrono\Time
	 */

	public static function createFromDOSTimestamp($timestamp, $timeZone = null)
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
	 * Returns new Time object formatted according to the specified format.
	 *
	 * @access  public
	 * @param   string                     $format    The format that the passed in string should be in
	 * @param   string                     $time      String representing the time
	 * @param   null|string|\DateTimeZone  $timeZone  A valid time zone or a DateTimeZone object
	 * @return  \mako\chrono\Time
	 */

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
	 * Sets the time zone for the Time object
	 *
	 * @access  public
	 * @param   string|\DateTimeZone  $timeZone  A valid time zone or a DateTimeZone object
	 * @return  \mako\chrono\Time
	 */

	public function setTimeZone($timeZone)
	{
		if(($timeZone instanceof DateTimeZone) === false)
		{
			$timeZone = new DateTimeZone($timeZone);
		}

		return parent::setTimeZone($timeZone);
	}

	/**
	 * Move forward in time by x seconds.
	 *
	 * @access  public
	 * @param   int                $seconds  Number of seconds
	 * @return  \mako\chrono\Time
	 */

	public function forward($seconds)
	{
		$this->setTimestamp($this->getTimestamp() + $seconds);

		return $this;
	}

	/**
	 * Move backward in time by x seconds.
	 *
	 * @access  public
	 * @param   int                $seconds  Number of seconds
	 * @return  \mako\chrono\Time
	 */

	public function rewind($seconds)
	{
		$this->setTimestamp($this->getTimestamp() - $seconds);

		return $this;
	}

	/**
	 * Returns the DOS timestamp.
	 *
	 * @access  public
	 * @return  int
	 */

	public function getDOSTimestamp()
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
	 * @access  public
	 * @return  boolean
	 */

	public function isLeapYear()
	{
		$year = $this->format('Y');

		if($year % 400 === 0 || ($year % 4 === 0 && $year % 100 !== 0))
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns the number of days in the current month.
	 *
	 * @access  public
	 * @return  int
	 */

	public function daysInMonth()
	{
		$days =
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
			31
		];

		return $days[$this->format('n') - 1];
	}

	/**
	 * Returns a formatted date string according to current locale settings.
	 *
	 * @access  public
	 * @param   string  $format  Date format
	 * @return  string
	 */

	public function formatLocalized($format)
	{
		return strftime($format, $this->getTimestamp());
	}
}