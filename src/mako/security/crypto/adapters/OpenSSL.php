<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security\crypto\adapters;

/**
 * OpenSSL adapter.
 *
 * @author  Frederic G. Østby
 */

class OpenSSL implements \mako\security\crypto\adapters\AdapterInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Key used to encrypt/decrypt data.
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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $key     Encryption key
	 * @param   int     $cipher  (optional) Cipher
	 */

	public function __construct($key, $cipher = null)
	{
		$this->key = $key;

		$this->cipher = $cipher ?: 'AES-256-OFB';

		$this->ivSize = openssl_cipher_iv_length($this->cipher);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Encrypts data.
	 *
	 * @access  public
	 * @param   string  $data  Data to encrypt
	 * @return  string
	 */
	
	public function encrypt($data)
	{
		$iv = openssl_random_pseudo_bytes($this->ivSize);

		return base64_encode($iv . openssl_encrypt($data, $this->cipher, $this->key, 0, $iv));
	}

	/**
	 * Decrypts data.
	 *
	 * @access  public
	 * @param   string  $data  Data to decrypt
	 * @return  string
	 */
	
	public function decrypt($data)
	{
		$data = base64_decode($data, true);
		
		if($data === false)
		{
			return false;
		}

		$iv = substr($data, 0, $this->ivSize);
		
		$data = substr($data, $this->ivSize);

		return openssl_decrypt($data, $this->cipher, $this->key, 0, $iv);
	}
}