<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security;

use SensitiveParameter;

use function hash_equals;
use function hash_hmac;
use function mb_substr;

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
		$validated = mb_substr($string, static::MAC_LENGTH, encoding: '8bit');

		if (hash_equals($this->getSignature($validated), mb_substr($string, 0, static::MAC_LENGTH, '8bit'))) {
			return $validated;
		}

		return false;
	}
}
