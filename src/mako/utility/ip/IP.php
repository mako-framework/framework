<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\utility\ip;

use mako\utility\ip\IPv4;
use mako\utility\ip\IPv6;

/**
 * IP utilities.
 *
 * @author  Frederic G. Østby
 */
class IP
{
	/**
	 * Checks if an IP is in the specified range.
	 *
	 * @access  public
	 * @param   string   $ip    IP address
	 * @param   string   $range Ip address or IP range
	 * @return  boolean
	 */
	public static function inRange(string $ip, string $range): bool
	{
		return strpos($ip, '.') === false ? IPv6::inRange($ip, $range) : IPv4::inRange($ip, $range);
	}
}