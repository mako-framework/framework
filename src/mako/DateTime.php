<?php

namespace mako;

use \mako\I18n;
use \DateTimeZone;

/**
 * Extension of the PHP DateTime class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class DateTime extends \DateTime
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $time      (optional) A date/time string
	 * @param   mixed   $timeZone  (optional) A valid time zone or a DateTimeZone object
	 */

	public function __construct($time = 'now', $timeZone = null)
	{
		if($timeZone !== null && !is_object($timeZone))
		{
			$timeZone = new DateTimeZone($timeZone);
		}

		parent::__construct($time, $timeZone);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns a new DateTime object.
	 * 
	 * @access  public
	 * @param   mixed           $timeZone  (optional) A valid time zone or a DateTimeZone object
	 * @return  \mako\DateTime
	 */

	public static function now($timeZone = null)
	{
		return new static('now', $timeZone);
	}

	/**
	 * Returns new DateTime object according to the specified date.
	 * 
	 * @access  public
	 * @param   int             $year      Year
	 * @param   int             $month     (optional) Month (1 to 12)
	 * @param   int             $day       (optional) Day of month (1 to 31)
	 * @param   string          $timeZone  (optional) A valid time zone or a DateTimeZone object
	 * @return  \mako\DateTime
	 */

	public static function createFromDate($year, $month = null, $day = null, $timeZone = null)
	{
		$now = static::now($timeZone);

		return $now->setDate($year, $month ?: $now->format('m'), $day ?: $now->format('d'));
	}

	/**
	 * Returns new DateTime object according to the specified timestamp.
	 * 
	 * @access  public
	 * @param   int             $timestamp  Unix timestamp
	 * @param   string          $timeZone   (optional) A valid time zone or a DateTimeZone object
	 * @return  \mako\DateTime
	 */

	public static function createFromTimestamp($timestamp, $timeZone = null)
	{
		$dateTime = new static('now', $timeZone);

		$dateTime->setTimestamp($timestamp);

		return $dateTime;
	}

	/**
	 * Returns new DateTime object according to the specified DOS timestamp.
	 * 
	 * @access  public
	 * @param   int             $timestamp  DOS timestamp
	 * @param   string          $timeZone   (optional) A valid time zone or a DateTimeZone object
	 * @return  \mako\DateTime
	 */

	public static function createFromDosTimestamp($timestamp, $timeZone = null)
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
	 * Returns new DateTime object formatted according to the specified format.
	 * 
	 * @access  public
	 * @param   string          $format    The format that the passed in string should be in
	 * @param   string          $time      String representing the time
	 * @param   string          $timeZone  (optional) A valid time zone or a DateTimeZone object
	 * @return  \mako\DateTime
	 */

	public static function createFromFormat($format, $time, $timeZone = null)
	{
		if($timeZone !== null)
		{
			if(!is_object($timeZone))
			{
				$timeZone = new DateTimeZone($timeZone);
			}

			$dateTime = parent::createFromFormat($format, $time, $timeZone);
		}
		else
		{
			$dateTime = parent::createFromFormat($format, $time);
		}

		return static::createFromTimestamp($dateTime->getTimestamp(), $dateTime->getTimeZone());
	}

	/**
	 * Sets the time zone for the DateTime object
	 * 
	 * @access  public
	 * @param   mixed           $timeZone  A valid time zone or a DateTimeZone object
	 * @return  \mako\DateTime
	 */

	public function setTimeZone($timeZone)
	{
		if(!is_object($timeZone))
		{
			$timeZone = new DateTimeZone($timeZone);
		}

		return parent::setTimeZone($timeZone);
	}

	/**
	 * Returns a list of time zones where the key is
	 * a valid PHP time zone while the value is a presentable name.
	 * 
	 * @access  public
	 * @return  array
	 */

	public static function getTimeZones()
	{
		$timeZones = array();

		foreach(DateTimeZone::listIdentifiers() as $timeZone)
		{
			$timeZones[$timeZone] = str_replace('_', ' ', $timeZone);
		}

		return $timeZones;
	}

	/**
	 * Returns an array of grouped time zones where the key is
	 * a valid PHP timezone while the value is a presentable name.
	 * 
	 * @access  public
	 * @return  array
	 */

	public static function getGroupedTimeZones()
	{
		$timeZones = array();

		foreach(DateTimeZone::listIdentifiers() as $timeZone)
		{
			list($group, $city) = explode('/', $timeZone, 2) + array(null, null);

			$timeZones[$group][$timeZone] = str_replace('_', ' ', $city);
		}

		unset($timeZones['UTC']);

		return $timeZones;
	}

	/**
	 * Move forward in time by x seconds.
	 * 
	 * @access  public
	 * @param   int             $seconds  Number of seconds
	 * @return  \mako\DateTime
	 */

	public function moveForward($seconds)
	{
		$this->setTimestamp($this->getTimestamp() + $seconds);

		return $this;
	}

	/**
	 * Move backward in time by x seconds.
	 * 
	 * @access  public
	 * @param   int             $seconds  Number of seconds
	 * @return  \mako\DateTime
	 */

	public function moveBackward($seconds)
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

	public function getDosTimestamp()
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
		$days = array
		(
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
		);
		 
		return $days[$this->format('n') - 1];
	}

	/**
	 * Returns a "fuzzy" date.
	 *
	 * @access  public
	 * @param   boolean  $fullFuzzy    (optional) Enable full fuzziness
	 * @param   string   $dateFormat   (optional) Date format used for non-fuzy dates
	 * @param   string   $clockFormat  (optional) Clock format for fuzzy dates - set to false to disable
	 * @return  string
	 */

	public function fuzzy($fullFuzzy = false, $dateFormat = 'Y-m-d', $clockFormat = ', H:i a')
	{
		$timestamp = $this->getTimestamp();

		$now  = time();
		$diff = max($now - $timestamp, 0);
		
		if($diff < 120)
		{
			 return I18n::get('datetime.minute_ago');
		}
		elseif($diff < 3600)
		{
			return I18n::get('datetime.minutes_ago', array($diff / 60));
		}
		
		if($fullFuzzy === true)
		{
			// Full fuzzy
			
			if($diff < 7200)
			{
				return I18n::get('datetime.hour_ago');
			}
			elseif($diff < 86400)
			{
				return I18n::get('datetime.hours_ago', array($diff / 3600));
			}
			elseif($diff < 172800)
			{
				$date = I18n::get('datetime.day_ago');
			}
			elseif($diff < 604800)
			{
				$date = I18n::get('datetime.days_ago', array($diff / 86400));
			}
			elseif($diff < 1209600)
			{
				$date = I18n::get('datetime.week_ago');
			}
			elseif($diff < 3024000)
			{
				$date = I18n::get('datetime.weeks_ago', array($diff / 604800));
			}
			else
			{
				$date = $this->format($dateFormat);
			}
		}
		else
		{
			// Kinda fuzzy
			
			$compare = static::createFromTimestamp($timestamp)->format('jny');

			if(static::now()->format('jny') == $compare)
			{
				$date = I18n::get('datetime.today');
			}
			elseif(static::now()->moveBackward(86400)->format('jny') == $compare)
			{
				$date = I18n::get('datetime.yesterday');
			}
			else
			{
				$date = $this->format($dateFormat);
			}
		}
		
		if($clockFormat !== false)
		{
			$date .= $this->format($clockFormat);
		}
		
		return $date;
	}
}

/** -------------------- End of file -------------------- **/