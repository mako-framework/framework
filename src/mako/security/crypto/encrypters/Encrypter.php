<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\crypto\encrypters;

use function hash_pbkdf2;

/**
 * Base encrypter.
 */
abstract class Encrypter
{
	/**
	 * Derivation hash.
	 *
	 * @var string
	 */
	protected const DERIVATION_HASH = 'sha256';

	/**
	 * Derivation iterations.
	 *
	 * @var int
	 */
	protected const DERIVATION_ITERATIONS = 1024;

	/**
	 * Generate a PBKDF2 key derivation of a supplied key.
	 */
	protected function deriveKey(string $key, string $salt, int $keySize): string
	{
		return hash_pbkdf2(static::DERIVATION_HASH, $key, $salt, static::DERIVATION_ITERATIONS, $keySize, true);
	}
}
