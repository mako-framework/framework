<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security;

use SensitiveParameter;

use function bin2hex;
use function hex2bin;
use function mb_substr;
use function random_bytes;
use function str_starts_with;

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
			return hex2bin(mb_substr($key, 4, encoding: '8bit'));
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
