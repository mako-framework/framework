<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security\crypto\encrypters;

/**
 * Encrypter interface.
 *
 * @author  Frederic G. Østby
 */

interface EncrypterInterface
{
	/**
	 * Encrypts string.
	 *
	 * @access  public
	 * @param   string  $string  String to encrypt
	 * @return  string
	 */

	public function encrypt($string);

	/**
	 * Decrypts string.
	 *
	 * @access  public
	 * @param   string          $string  String to decrypt
	 * @return  string|boolean
	 */

	public function decrypt($string);
}