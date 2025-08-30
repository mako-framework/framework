<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\crypto\encrypters;

use SensitiveParameter;

use function hash_pbkdf2;

/**
 * Base encrypter.
 */
abstract class Encrypter
{
	/**
	 * Derivation hash.
	 */
	protected const string DERIVATION_HASH = 'sha256';

	/**
	 * Default derivation iterations.
	 */
	protected const int DEFAULT_DERIVATION_ITERATIONS = 600_000;

	/**
	 * Derivation iterations.
	 */
	protected ?int $keyDerivationIterations = null;

	/**
	 * Returns a PBKDF2 key derivation of the supplied key.
	 */
	protected function deriveKey(#[SensitiveParameter] string $key, #[SensitiveParameter] string $salt, int $keySize): string
	{
		return hash_pbkdf2(
			static::DERIVATION_HASH,
			$key,
			$salt,
			$this->keyDerivationIterations ?? static::DEFAULT_DERIVATION_ITERATIONS,
			$keySize,
			true
		);
	}
}
