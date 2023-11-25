<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\crypto\encrypters;

use SensitiveParameter;

/**
 * Encrypter interface.
 */
interface EncrypterInterface
{
	/**
	 * Encrypts string.
	 */
	public function encrypt(#[SensitiveParameter] string $string): string;

	/**
	 * Decrypts string.
	 *
	 * @return false|string
	 */
	public function decrypt(#[SensitiveParameter] string $string);
}
