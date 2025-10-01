<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility\ip;

use function explode;
use function inet_pton;
use function pack;
use function str_contains;
use function str_pad;
use function str_repeat;

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

			if ($netmask < 1 || $netmask > 128) {
				return false;
			}
		}

		$ip    = inet_pton($ip);
		$range = inet_pton($range);

		if ($ip === false || $range === false) {
			return false;
		}

		$binNetmask = str_repeat('f', (int) ($netmask / 4)) . match ($netmask % 4) {
			1       => '8',
			2       => 'c',
			3       => 'e',
			default => '',
 		};

		return ($ip & pack('H*', str_pad($binNetmask, 32, '0'))) === $range;
	}
}
