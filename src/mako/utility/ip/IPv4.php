<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility\ip;

use function explode;
use function ip2long;
use function str_contains;

/**
 * IPv4 utilities.
 */
class IPv4
{
	/**
	 * Checks if an IP is in the specified range.
	 */
	public static function inRange(string $ip, string $range): bool
	{
		if (str_contains($range, '/') === false) {
			$netmask = 32;
		}
		else {
			[$range, $netmask] = explode('/', $range, 2);

			if ($netmask < 0 || $netmask > 32) {
				return false;
			}
		}

		if (($ip2Long = ip2long($ip)) === false || ($range2Long = ip2long($range)) === false) {
			return false;
		}

		$netmaskDecimal = ~ ((2 ** (32 - $netmask)) - 1);

		return ($ip2Long & $netmaskDecimal) === ($range2Long & $netmaskDecimal);
	}
}
