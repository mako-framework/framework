<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\crypto\encrypters;

/**
 * Encrypter interface.
 */
interface EncrypterInterface
{
	/**
	 * Encrypts string.
	 */
	public function encrypt(string $string): string;

	/**
	 * Decrypts string.
	 *
	 * @return false|string
	 */
	public function decrypt(string $string);
}
