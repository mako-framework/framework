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
	public function encrypt($string);
	public function decrypt($string);
}