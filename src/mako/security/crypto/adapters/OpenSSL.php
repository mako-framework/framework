<?php

namespace mako\security\crypto\adapters;

use \RuntimeException;

/**
 * OpenSSL cryptography adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class OpenSSL extends \mako\security\crypto\adapters\Adapter
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
	 * @param   array   $config  Configuration
	 */
	
	public function __construct(array $config)
	{
		if(extension_loaded('openssl') === false)
		{
			throw new RuntimeException(vsprintf("%s(): OpenSSL is not available.", [__METHOD__]));
		}
		
		$this->key    = $config['key'];
		$this->cipher = $config['cipher'];
		$this->ivSize = openssl_cipher_iv_length($this->cipher);
	}
	
	//---------------------------------------------
	// Class methods
	//---------------------------------------------
	
	/**
	 * Encrypts string.
	 *
	 * @access  public
	 * @param   string  $string  String to encrypt
	 * @return  string
	 */
	
	public function encrypt($string)
	{
		$iv = openssl_random_pseudo_bytes($this->ivSize);

		return base64_encode($iv . openssl_encrypt($string, $this->cipher, $this->key, 0, $iv));
	}
	
	/**
	 * Decrypts string.
	 *
	 * @access  public
	 * @param   string  $string  String to decrypt
	 * @return  string
	 */
	
	public function decrypt($string)
	{
		$string = base64_decode($string, true);
		
		if($string === false)
		{
			return false;
		}

		$iv = substr($string, 0, $this->ivSize);
		
		$string = substr($string, $this->ivSize);

		return openssl_decrypt($string, $this->cipher, $this->key, 0, $iv);
	}
}

/** -------------------- End of file -------------------- **/