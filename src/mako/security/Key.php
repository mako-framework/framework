<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security;

use mako\security\exceptions\SecurityException;
use SensitiveParameter;

use function bin2hex;
use function ctype_xdigit;
use function hex2bin;
use function random_bytes;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * Key helpers.
 */
class Key
{
	/**
	 * Converts a binary key into its hexadecimal representation.
	 */
	public static function encode(#[SensitiveParameter] string $key): string
	{
		return 'hex:' . bin2hex($key);
	}

	/**
	 * Converts a hexadecimal key into its binary representation.
	 */
	public static function decode(#[SensitiveParameter] string $key): string
	{
		if (str_starts_with($key, 'hex:')) {
			$hex = substr($key, 4);

			// Ensure that the key is valid hex of even length before attempting to decode it,
			// as hex2bin() returns false (with a warning) on invalid or odd-length hex strings.

			if (ctype_xdigit($hex) === false || (strlen($hex) % 2) !== 0) {
				throw new SecurityException('Invalid hex-encoded key.');
			}

			return hex2bin($hex);
		}

		return $key;
	}

	/**
	 * Generates a binary key.
	 */
	public static function generate(int $length = 32): string
	{
		return random_bytes($length);
	}

	/**
	 * Generates a hex encoded key.
	 */
	public static function generateEncoded(int $length = 32): string
	{
		return static::encode(static::generate($length));
	}
}
