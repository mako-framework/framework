<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeZone;

/**
 * Extension of the PHP DateTimeZone class.
 *
 * @author  Frederic G. Østby
 */

class TimeZone extends DateTimeZone
{
	/**
	 * Returns a list of time zones where the key is
	 * a valid PHP time zone while the value is a presentable name.
	 *
	 * @access  public
	 * @return  array
	 */

	public static function getTimeZones()
	{
		$timeZones = [];

		foreach(static::listIdentifiers() as $timeZone)
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
		$timeZones = [];

		foreach(static::listIdentifiers() as $timeZone)
		{
			list($group, $city) = explode('/', $timeZone, 2) + [null, null];

			$timeZones[$group][$timeZone] = str_replace('_', ' ', $city);
		}

		unset($timeZones['UTC']);

		return $timeZones;
	}
}