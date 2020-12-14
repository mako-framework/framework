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
	 *
	 * @param  string $string String to encrypt
	 * @return string
	 */
	public function encrypt(string $string): string;

	/**
	 * Decrypts string.
	 *
	 * @param  string       $string String to decrypt
	 * @return false|string
	 */
	public function decrypt(string $string);
}
