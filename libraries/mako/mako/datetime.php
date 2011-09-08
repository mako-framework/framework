<?php

namespace mako
{
	use \mako\I18n;
	
	/**
	* Collection of date and time related methods.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class DateTime
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* Number of seconds in a minute.
		*/
		
		const MINUTE = 60;
		
		/**
		* Number of seconds in an hour.
		*/
		
		const HOUR = 3600;
		
		/**
		* Number of seconds in a day.
		*/
		
		const DAY = 86400;
		
		/**
		* Number of seconds in a week.
		*/
		
		const WEEK = 604800;
		
		/**
		* Number of seconds in a month.
		*/
		
		const MONTH = 2629744;
		
		/**
		* Number of seconds in a year.
		*/
		
		const YEAR = 31556926;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Protected constructor since this is a static class.
		*
		* @access  protected
		*/

		protected function __construct()
		{
			// Nothing here
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Returns a localized date (based on the locale).
		*
		* @access  public
		* @param   string  Date format (http://php.net/manual/en/function.strftime.php)
		* @param   int     Unix timestamp
		* @param   int     (optional) Offset in seconds
		* @return  string
		*/

		public static function utcDate($dateFormat, $timestamp, $offset = 0)
		{
			return gmstrftime($dateFormat, ($timestamp + $offset));
		}

		/**
		* Returns an array with all UTC timezones (keys are offset seconds from UTC).
		*
		* @access  public
		* @return  array
		*/

		public static function utcTimeZones()
		{
			$timeZones = array
			(
				'-43200' => 'UTC-12:00',
				'-39600' => 'UTC-11:00',
				'-36000' => 'UTC-10:00',
				'-34200' => 'UTC-9:30',
				'-32400' => 'UTC-9:00',
				'-28800' => 'UTC-8:00',
				'-25200' => 'UTC-7:00',
				'-21600' => 'UTC-6:00',
				'-18000' => 'UTC-5:00',
				'-16200' => 'UTC-4:30',
				'-14400' => 'UTC-4:00',
				'-12600' => 'UTC-3:30',
				'-10800' => 'UTC-3:00',
				'-7200'  => 'UTC-2:00',
				'-3600'  => 'UTC-1:00',
				'0'      => 'UTC',
				'3600'   => 'UTC+1:00',
				'7200'   => 'UTC+2:00',
				'10800'  => 'UTC+3:00',
				'12600'  => 'UTC+3:30',
				'14400'  => 'UTC+4:00',
				'16200'  => 'UTC+4:30',
				'18000'  => 'UTC+5:00',
				'19800'  => 'UTC+5:30',
				'20700'  => 'UTC+5:45',
				'21600'  => 'UTC+6:00',
				'23400'  => 'UTC+6:30',
				'25200'  => 'UTC+7:00',
				'28800'  => 'UTC+8:00',
				'31500'  => 'UTC+8:45',
				'32400'  => 'UTC+9:00',
				'34200'  => 'UTC+9:30',
				'36000'  => 'UTC+10:00',
				'37800'  => 'UTC+10:30',
				'39600'  => 'UTC+11:00',
				'41400'  => 'UTC+11:30',
				'43200'  => 'UTC+12:00',
				'45900'  => 'UTC+12:45',
				'46800'  => 'UTC+13:00',
				'50400'  => 'UTC+14:00'
			);

			return $timeZones;
		}

		/**
		* Returns a "fuzzy date".
		*
		* @access  public
		* @param   int      Unix timestamp
		* @param   string   Date format used for non-fuzy dates
		* @param   int      (optional) Offset in seconds
		* @param   boolean  (optional) Enable full fuzziness
		* @param   string   (optional) Clock format for fuzzy dates - set to false to disable
		* @return  string
		*/

		public static function fuzzy($timestamp, $dateFormat, $offset = 0, $fullFuzzy = false, $clockFormat = ', %R %p')
		{
			$now  = time();
			$diff = max($now - $timestamp, 0);
			
			if($diff < 120)
			{
				 return I18n::getText('a minute ago');
			}
			else if($diff < 3600)
			{
				return I18n::getText('%u minutes ago', array($diff / 60));
			}
			
			if($fullFuzzy === true)
			{
				// Full fuzzy
				
				if($diff < 7200)
				{
					return I18n::getText('an hour ago');
				}
				else if($diff < 86400)
				{
					return I18n::getText('%u hours ago', array($diff / 3600));
				}
				else if($diff < 172800)
				{
					$date = I18n::getText('a day ago');
				}
				else if($diff < 604800)
				{
					$date = I18n::getText('%u days ago', array($diff / 86400));
				}
				else if($diff < 1209600)
				{
					$date = I18n::getText('a week ago');
				}
				else if($diff < 3024000)
				{
					$date = I18n::getText('%u weeks ago', array($diff / 604800));
				}
				else
				{
					return static::utcDate($dateFormat, $timestamp, $offset);
				}
			}
			else
			{
				// Kinda fuzzy
				
				$compare = gmdate('jny', ($timestamp + $offset));

				if(gmdate('jny', ($now + $offset)) == $compare)
				{
					$date = I18n::getText('today');
				}
				else if(gmdate('jny', (($now + $offset) - 86400)) == $compare)
				{
					$date = I18n::getText('yesterday');
				}
				else
				{
					return static::utcDate($dateFormat, $timestamp, $offset);
				}
			}
			
			if($clockFormat !== false)
			{
				$date .= static::utcDate($clockFormat, $timestamp, $offset);
			}
			
			return $date;
		}

		/**
		* Returns true if year is leap year and false if not.
		*
		* @access  public
		* @param   int      The year you want to check
		* @return  boolean
		*/

		public static function isLeapYear($year)
		{
			if($year % 400 === 0 || ($year % 4 === 0 && $year % 100 !== 0))
			{
				return true;
			}

			return false;
		}

		/**
		* Converts a UNIX timestamp to a DOS timestamp.
		*
		* @access  public
		* @param   int     Unix timestamp
		* @return  int
		*/

		public static function unix2Dos($timestamp = null)
		{
			$time = ($timestamp === null) ? getdate() : getdate($timestamp);

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
		* Converts a DOS timestamp to a UNIX timestamp.
		*
		* @access  public
		* @param   int     DOS timestamp
		* @return  int
		*/

		public static function dos2Unix($timestamp)
		{
			$year     = (($timestamp >> 25) & 0x7f) + 1980;
			$mon      = ($timestamp >> 21) & 0x0f;
			$mday     = ($timestamp >> 16) & 0x1f;
			$hours    = ($timestamp >> 11) & 0x1f;
			$minutes  = ($timestamp >> 5) & 0x3f;
			$seconds  = 2 * ($timestamp & 0x1f);

			return mktime($hours, $minutes, $seconds, $mon, $mday, $year);
		}
	}
}

/** -------------------- End of file --------------------**/