<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security;

/**
 * Key helpers.
 *
 * @author  Frederic G. Østby
 */
class Key
{
	/**
	 * Converts a binary key into its hexadecimal representation.
	 *
	 * @access  public
	 * @param   string  $key  Binary key
	 * @return  string
	 */
	public static function encode(string $key): string
	{
		return 'hex:' . bin2hex($key);
	}

	/**
	 * Converts a hexadecimal key into its binary representation.
	 *
	 * @access  public
	 * @param   string  $key  Encoded key
	 * @return  string
	 */
	public static function decode(string $key): string
	{
		if(strpos($key, 'hex:') === 0)
		{
			return hex2bin(mb_substr($key, 4, null, '8bit'));
		}

		return $key;
	}

	/**
	 * Generates an encryption key.
	 *
	 * @access  public
	 * @param   int     $length  Key length
	 * @return  string
	 */
	public static function generate(int $length = 32): string
	{
		return random_bytes($length);
	}

	/**
	 * Generates a hex encoded 256-bit encryption key.
	 *
	 * @access  public
	 * @param   int     $length  Key length
	 * @return  string
	 */
	public static function generateEncoded(int $length = 32): string
	{
		return static::encode(static::generate($length));
	}
}