<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\crypto\encrypters;

use Override;
use SensitiveParameter;

use function base64_decode;
use function base64_encode;
use function mb_substr;
use function openssl_cipher_iv_length;
use function openssl_decrypt;
use function openssl_encrypt;
use function openssl_random_pseudo_bytes;

/**
 * OpenSSL encrypter.
 */
class OpenSSL extends Encrypter implements EncrypterInterface
{
	/**
	 * Initialization vector size.
	 */
	protected int $ivSize;

	/**
	 * Constructor.
	 */
	public function __construct(
		#[SensitiveParameter] protected string $key,
		protected string $cipher = 'AES-256-CTR'
	) {
		$this->ivSize = openssl_cipher_iv_length($this->cipher);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function encrypt(#[SensitiveParameter] string $string): string
	{
		$iv = openssl_random_pseudo_bytes($this->ivSize);

		$key = $this->deriveKey($this->key, $iv, 32);

		return base64_encode($iv . openssl_encrypt($string, $this->cipher, $key, iv: $iv));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function decrypt(#[SensitiveParameter] string $string): false|string
	{
		$string = base64_decode($string, true);

		if ($string === false) {
			return false;
		}

		$iv = mb_substr($string, 0, $this->ivSize, '8bit');

		$string = mb_substr($string, $this->ivSize, encoding: '8bit');

		$key = $this->deriveKey($this->key, $iv, 32);

		return openssl_decrypt($string, $this->cipher, $key, iv: $iv);
	}
}
