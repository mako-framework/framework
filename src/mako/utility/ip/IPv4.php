<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\utility\ip;

/**
 * IPv4 utilities.
 *
 * @author  Frederic G. Østby
 */
class IPv4
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
		if(strpos($range, '/') === false)
		{
			$netmask = 32;
		}
		else
		{
			list($range, $netmask) = explode('/', $range, 2);

			if($netmask < 0 || $netmask > 32)
			{
				return false;
			}
		}

		if(($ip2Long = ip2long($ip)) === false || ($range2Long = ip2long($range)) === false)
		{
			return false;
		}

		$netmaskDecimal = ~ (pow(2, (32 - $netmask)) - 1);

		return ($ip2Long & $netmaskDecimal) === ($range2Long & $netmaskDecimal);
	}
}