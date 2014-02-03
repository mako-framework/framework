<?php

namespace mako\utility;

use \DateTime as PHPDateTime;

use \mako\i18n\I18n;

/**
 * Makes data more human friendly.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Humanizer
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * I18n instance.
	 * 
	 * @var \mako\i18n\I18n
	 */

	protected $i18n;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\i18n\I18n  $i18n  I18n instance
	 */

	public function __construct(I18n $i18n)
	{
		$this->i18n = $i18n;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns a human friendly representation of the date.
	 * 
	 * @access  public
	 * @param   \DateTime  $dateTime    DateTime object
	 * @param   string     $dateFormat  (optional) Default date format
	 * @return  string
	 */

	public function day(PHPDateTime $dateTime, $dateFormat = 'Y-m-d, H:i')
	{
		if($dateTime->format('Y-m-d') === date('Y-m-d'))
		{
			return $this->i18n->get('humanizer.today');
		}
		elseif($dateTime->format('Y-m-d') === date('Y-m-d', time() - (60 * 60 * 24)))
		{
			return $this->i18n->get('humanizer.yesterday');
		}
		elseif($dateTime->format('Y-m-d') === date('Y-m-d', time() + (60 * 60 * 24)))
		{
			return $this->i18n->get('humanizer.tomorrow');
		}
		else
		{
			return $dateTime->format($dateFormat);
		}
	}

	/**
	 * Returns a human friendly representation of the time.
	 * 
	 * @access  public
	 * @param   \DateTime  $dateTime     DateTime object
	 * @param   string     $dateFormat   (optional) Default date format
	 * @param   string     $clockFormat  (optional) Default clock format
	 * @return  string
	 */

	public function time(PHPDateTime $dateTime, $dateFormat = 'Y-m-d, H:i', $clockFormat = ', H:i')
	{
		$diff = time() - $dateTime->getTimestamp();

		if($diff < 0)
		{
			// Our date is in the future

			$diff = abs($diff);

			if($diff < 120)
			{
				return $this->i18n->get('humanizer.in_minute');
			}
			elseif($diff < 3600)
			{
				return $this->i18n->get('humanizer.in_minutes', [$diff / 60]);
			}
			elseif($dateTime->format('Y-m-d') === date('Y-m-d'))
			{
				return $this->i18n->get('humanizer.today') . (!empty($clockFormat) ? $dateTime->format($clockFormat) : '');
			}
			elseif($dateTime->format('Y-m-d') === date('Y-m-d', time() + (60 * 60 * 24)))
			{
				return $this->i18n->get('humanizer.tomorrow') . (!empty($clockFormat) ? $dateTime->format($clockFormat) : '');
			}
		}
		else
		{
			// Our date is in the past

			if($diff < 120)
			{
				return $this->i18n->get('humanizer.minute_ago');
			}
			elseif($diff < 3600)
			{
				return $this->i18n->get('humanizer.minutes_ago', [$diff / 60]);
			}
			elseif($dateTime->format('Y-m-d') === date('Y-m-d'))
			{
				return $this->i18n->get('humanizer.today') . (!empty($clockFormat) ? $dateTime->format($clockFormat) : '');
			}
			elseif($dateTime->format('Y-m-d') === date('Y-m-d', time() - (60 * 60 * 24)))
			{
				return $this->i18n->get('humanizer.yesterday') . (!empty($clockFormat) ? $dateTime->format($clockFormat) : '');
			}
		}

		// None of the above so just return the default date format

		return $dateTime->format($dateFormat);
	}
}

/** -------------------- End of file -------------------- **/