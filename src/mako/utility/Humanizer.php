<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\utility;

use DateTimeInterface;

use mako\i18n\I18n;

/**
 * Makes data more human friendly.
 *
 * @author  Frederic G. Østby
 */

class Humanizer
{
	/**
	 * I18n instance.
	 *
	 * @var \mako\i18n\I18n
	 */

	protected $i18n;

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

	/**
	 * Returns a human friendly file size.
	 *
	 * @access  public
	 * @param   int      $size    File size in bytes
	 * @param   boolean  $binary  True to use binary suffixes and false to use decimal suffixes
	 * @return  string
	 */

	public function fileSize($size, $binary = true)
	{
		if($size > 0)
		{
			if($binary === true)
			{
				$base  = 1024;
				$terms = ['byte', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
			}
			else
			{
				$base  = 1000;
				$terms = ['byte', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
			}

			$e = floor(log($size, $base));

			return round($size / pow($base, $e), 2) . ' ' . $terms[$e];
		}
		else
		{
			return '0 byte';
		}
	}

	/**
	 * Returns a human friendly representation of the date.
	 *
	 * @access  public
	 * @param   \DateTimeInterface  $dateTime    DateTime object
	 * @param   string              $dateFormat  Default date format
	 * @return  string
	 */

	public function day(DateTimeInterface $dateTime, $dateFormat = 'Y-m-d, H:i')
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
	 * @param   \DateTimeInterface  $dateTime     DateTime object
	 * @param   string              $dateFormat   Default date format
	 * @param   string              $clockFormat  Default clock format
	 * @return  string
	 */

	public function time(DateTimeInterface $dateTime, $dateFormat = 'Y-m-d, H:i', $clockFormat = ', H:i')
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