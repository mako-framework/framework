<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\crypto\encrypters;

use mako\security\crypto\encrypters\Encrypter;
use mako\security\crypto\encrypters\EncrypterInterface;

/**
 * OpenSSL encrypter.
 *
 * @author Frederic G. Østby
 */
class OpenSSL extends Encrypter implements EncrypterInterface
{
	/**
	 * Key used to encrypt/decrypt string.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The cipher method to use for encryption.
	 *
	 * @var string
	 */
	protected $cipher;

	/**
	 * Initialization vector size.
	 *
	 * @var string
	 */
	protected $ivSize;

	/**
	 * Constructor.
	 *
	 * @param string      $key    Encryption key
	 * @param string|null $cipher Cipher
	 */
	public function __construct(string $key, string $cipher = null)
	{
		$this->key = $key;

		$this->cipher = $cipher ?? 'AES-256-CTR';

		$this->ivSize = openssl_cipher_iv_length($this->cipher);
	}

	/**
	 * {@inheritdoc}
	 */
	public function encrypt(string $string): string
	{
		$iv = openssl_random_pseudo_bytes($this->ivSize);

		$key = $this->deriveKey($this->key, $iv, 32);

		return base64_encode($iv . openssl_encrypt($string, $this->cipher, $key, 0, $iv));
	}

	/**
	 * {@inheritdoc}
	 */
	public function decrypt(string $string)
	{
		$string = base64_decode($string, true);

		if($string === false)
		{
			return false;
		}

		$iv = mb_substr($string, 0, $this->ivSize, '8bit');

		$string = mb_substr($string, $this->ivSize, null, '8bit');

		$key = $this->deriveKey($this->key, $iv, 32);

		return openssl_decrypt($string, $this->cipher, $key, 0, $iv);
	}
}
