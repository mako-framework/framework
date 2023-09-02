<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility\ip;

use function strpos;

/**
 * IP utilities.
 */
class IP
{
	/**
	 * Checks if an IP is in the specified range.
	 */
	public static function inRange(string $ip, string $range): bool
	{
		return strpos($ip, '.') === false ? IPv6::inRange($ip, $range) : IPv4::inRange($ip, $range);
	}
}
