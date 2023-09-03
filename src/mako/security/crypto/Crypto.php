<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\crypto;

use mako\security\crypto\encrypters\EncrypterInterface;
use mako\security\crypto\exceptions\CryptoException;
use mako\security\Signer;

/**
 * Crypto wrapper.
 */
class Crypto
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected EncrypterInterface $adapter,
		protected Signer $signer
	)
	{}

	/**
	 * Encrypts string.
	 */
	public function encrypt(string $string): string
	{
		return $this->signer->sign($this->adapter->encrypt($string));
	}

	/**
	 * Decrypts string.
	 *
	 * @return false|string
	 */
	public function decrypt(string $string)
	{
		$string = $this->signer->validate($string);

		if($string === false)
		{
			throw new CryptoException('Ciphertex has been modified or an invalid authentication key has been provided.');
		}

		return $this->adapter->decrypt($string);
	}
}
