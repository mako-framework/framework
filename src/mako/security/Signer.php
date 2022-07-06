<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security;

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
	 *
	 * @var int
	 */
	public const MAC_LENGTH = 64;

	/**
	 * Constructor.
	 *
	 * @param string $secret Secret used to sign and validate strings
	 */
	public function __construct(
		protected string $secret
	)
	{}

	/**
	 * Returns the signature.
	 *
	 * @param  string $string The string you want to sign
	 * @return string
	 */
	protected function getSignature(string $string): string
	{
		return hash_hmac('sha256', $string, $this->secret);
	}

	/**
	 * Returns a signed string.
	 *
	 * @param  string $string The string you want to sign
	 * @return string
	 */
	public function sign(string $string): string
	{
		return "{$this->getSignature($string)}$string";
	}

	/**
	 * Returns the original string if the signature is valid or FALSE if not.
	 *
	 * @param  string       $string The string you want to validate
	 * @return false|string
	 */
	public function validate(string $string)
	{
		$validated = mb_substr($string, static::MAC_LENGTH, null, '8bit');

		if(hash_equals($this->getSignature($validated), mb_substr($string, 0, static::MAC_LENGTH, '8bit')))
		{
			return $validated;
		}

		return false;
	}
}
