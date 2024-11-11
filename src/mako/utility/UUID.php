<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use mako\utility\exceptions\UUIDException;

use function bin2hex;
use function chr;
use function dechex;
use function explode;
use function hash;
use function hex2bin;
use function hexdec;
use function microtime;
use function ord;
use function preg_match;
use function random_bytes;
use function sprintf;
use function str_replace;
use function str_split;
use function strlen;
use function substr;
use function vsprintf;

/**
 * Class that generates and validates UUIDs.
 */
class UUID
{
	/**
	 * DNS namespace.
	 */
	public const string DNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';

	/**
	 * URL namespace.
	 */
	public const string URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';

	/**
	 * ISO OID namespace.
	 */
	public const string OID = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';

	/**
	 * X.500 DN namespace.
	 */
	public const string X500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';

	/**
	 * The "nil" UUID.
	 */
	public const string NIL = '00000000-0000-0000-0000-000000000000';

	/**
	 * The "max" UUID.
	 */
	public const string MAX = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

	/**
	 * Checks if a UUID is valid.
	 */
	public static function validate(string $uuid): bool
	{
		$uuid = str_replace(['urn:uuid:', '{', '}'], '', $uuid);

		return (bool) preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $uuid);
	}

	/**
	 * Converts a UUID from its hexadecimal representation to a binary string.
	 */
	public static function toBinary(string $uuid): string
	{
		if (!static::validate($uuid)) {
			throw new UUIDException('The provided string is not a valid UUID.');
		}

		$hex = str_replace(['urn:uuid:', '{', '}', '-'], '', $uuid);

		$binary = '';

		for ($i = 0; $i < 32; $i += 2) {
			$binary .= chr(hexdec("{$hex[$i]}{$hex[$i + 1]}"));
		}

		return $binary;
	}

	/**
	 * Converts a binary UUID to its hexadecimal representation.
	 */
	public static function toHexadecimal(string $bytes): string
	{
		if (strlen($bytes) !== 16) {
			throw new UUIDException('The input must be exactly 16 bytes.');
		}

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
	}

	/**
	 * Returns a V3 UUID.
	 */
	public static function v3(string $namespace, string $name): string
	{
		$hash = hash('md5', static::toBinary($namespace) . $name);

		return sprintf(
			'%s-%s-%x-%x-%s',
			substr($hash, 0, 8),
			substr($hash, 8, 4),
			(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,
			(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
			substr($hash, 20, 12)
		);
	}

	/**
	 * Returns a V4 UUID.
	 */
	public static function v4(): string
	{
		$random = random_bytes(16);

		$random[6] = chr(ord($random[6]) & 0x0f | 0x40);

		$random[8] = chr(ord($random[8]) & 0x3f | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($random), 4));
	}

	/**
	 * Returns a sequential (COMB) v4 UUID.
	 */
	public static function v4Sequential(): string
	{
		[$usec, $sec] = explode(' ', microtime());

		$random = hex2bin(dechex((int) ($sec . substr($usec, 2, 5))) . bin2hex(random_bytes(10)));

		$random[6] = chr(ord($random[6]) & 0x0f | 0x40);

		$random[8] = chr(ord($random[8]) & 0x3f | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($random), 4));
	}

	/**
	 * Returns a V5 UUID.
	 */
	public static function v5(string $namespace, string $name): string
	{
		$hash = hash('sha1', static::toBinary($namespace) . $name);

		return sprintf(
			'%s-%s-%x-%x-%s',
			substr($hash, 0, 8),
			substr($hash, 8, 4),
			(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,
			(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
			substr($hash, 20, 12)
		);
	}
}
