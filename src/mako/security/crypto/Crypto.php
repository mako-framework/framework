<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\crypto;

use mako\security\crypto\encrypters\EncrypterInterface;
use mako\security\crypto\exceptions\CryptoException;
use mako\security\signer\exceptions\SignerException;
use mako\security\signer\Signer;
use SensitiveParameter;

/**
 * Crypto wrapper.
 */
class Crypto
{
	/**
	 * Constructor.
	 */
	public function __construct(
		public protected(set) EncrypterInterface $encrypter,
		public protected(set) Signer $signer
	) {
	}

	/**
	 * Encrypts string.
	 */
	public function encrypt(#[SensitiveParameter] string $string): string
	{
		return $this->signer->sign($this->encrypter->encrypt($string));
	}

	/**
	 * Decrypts string.
	 */
	public function decrypt(#[SensitiveParameter] string $string): false|string
	{
		try {
			$string = $this->signer->validateOrThrow($string);
		}
		catch (SignerException $e) {
			throw new CryptoException('Ciphertex has been modified or an invalid authentication key has been provided.', previous: $e);
		}

		return $this->encrypter->decrypt($string);
	}
}
