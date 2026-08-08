<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\signer;

use mako\security\signer\exceptions\SignerException;
use SensitiveParameter;

use function hash_equals;
use function hash_hmac;
use function substr;

/**
 * Signs and validates strings using MACs (message authentication codes).
 */
class Signer
{
	/**
	 * MAC length.
	 */
	protected const int MAC_LENGTH = 64;

	/**
	 * Constructor.
	 */
	public function __construct(
		#[SensitiveParameter] protected string $secret
	) {
	}

	/**
	 * Returns the signature.
	 */
	protected function getSignature(string $string): string
	{
		return hash_hmac('sha256', $string, $this->secret);
	}

	/**
	 * Returns a signed string.
	 */
	public function sign(string $string): string
	{
		return "{$this->getSignature($string)}$string";
	}

	/**
	 * Returns the original string if the signature is valid or FALSE if not.
	 */
	public function validate(string $string): false|string
	{
		$validated = substr($string, static::MAC_LENGTH);

		if (hash_equals($this->getSignature($validated), substr($string, 0, static::MAC_LENGTH))) {
			return $validated;
		}

		return false;
	}

	/**
	 * Returns the original string if the signature is valid or throws an exception if not.
	 */
	public function validateOrThrow(string $string): string
	{
		$validated = $this->validate($string);

		if ($validated === false) {
			throw new SignerException('The signed string has been modified or an invalid signing key has been provided.');
		}

		return $validated;
	}
}
