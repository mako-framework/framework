<?php

namespace mako\security\crypto;

use \RuntimeException;

/**
 * OpenSSL cryptography adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class OpenSSL extends \mako\security\crypto\Adapter
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
			throw new RuntimeException(vsprintf("%s(): OpenSSL is not available.", array(__METHOD__)));
		}
		
		$this->key      = $config['key'];
		$this->cipher   = $config['cipher'];
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
		return openssl_encrypt($string, $this->cipher, $this->key);
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
		return openssl_decrypt($string, $this->cipher, $this->key);
	}
}

/** -------------------- End of file -------------------- **/