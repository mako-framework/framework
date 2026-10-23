<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility\ip;

use function ctype_digit;
use function explode;
use function inet_pton;
use function intdiv;
use function pack;
use function str_contains;
use function str_pad;
use function str_repeat;
use function strlen;

/**
 * IPv6 utilities.
 */
class IPv6
{
	/**
	 * Checks if an IP is in the specified range.
	 */
	public static function inRange(string $ip, string $range): bool
	{
		if (str_contains($range, '/') === false) {
			$netmask = 128;
		}
		else {
			[$range, $netmask] = explode('/', $range, 2);

			if (!ctype_digit($netmask) || (int) $netmask > 128) {
				return false;
			}

			$netmask = (int) $netmask;
		}

		$ip    = inet_pton($ip);
		$range = inet_pton($range);

		if ($ip === false || $range === false || strlen($ip) !== 16 || strlen($range) !== 16) {
			return false;
		}

		$binNetmask = str_repeat('f', intdiv($netmask, 4)) . match ($netmask % 4) {
			1       => '8',
			2       => 'c',
			3       => 'e',
			default => '',
		};

		$mask = pack('H*', str_pad($binNetmask, 32, '0'));

		return ($ip & $mask) === ($range & $mask);
	}
}
