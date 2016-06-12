<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\utility\ip;

use Throwable;

/**
 * IPv6 utilities.
 *
 * @author  Frederic G. Østby
 */
class IPv6
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
			$netmask = 128;
		}
		else
		{
			list($range, $netmask) = explode('/', $range, 2);

			if($netmask < 1 || $netmask > 128)
			{
				return false;
			}
		}

		$binNetmask = str_repeat("f", $netmask / 4);

		switch($netmask % 4)
		{
			case 1:
				$binNetmask .= "8";
				break;
			case 2:
				$binNetmask .= "c";
				break;
			case 3:
				$binNetmask .= "e";
				break;
		}

		$binNetmask = pack("H*", str_pad($binNetmask, 32, '0'));

		try
		{
			return (inet_pton($ip) & $binNetmask) === inet_pton($range);
		}
		catch(Throwable $e)
		{
			return false;
		}
	}
}