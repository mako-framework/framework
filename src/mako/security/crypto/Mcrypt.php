<?php

namespace mako\security\crypto;

use \RuntimeException;

/**
 * Mcrypt cryptography adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Mcrypt extends \mako\security\crypto\Adapter
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------
	
	/**
	 * The cipher method to use for encryption.
	 *
	 * @var int
	 */
	
	protected $cipher;
	
	/**
	 * Key used to encrypt/decrypt data.
	 *
	 * @var string
	 */
	
	protected $key;
	
	/**
	 * Encryption mode.
	 *
	 * @var int
	 */
	
	protected $mode;
	
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
		if(extension_loaded('mcrypt') === false)
		{
			throw new RuntimeException(vsprintf("%s(): Mcrypt is not available.", array(__METHOD__)));
		}
		
		$maxSize = mcrypt_get_key_size($config['cipher'], $config['mode']);
		
		if(mb_strlen($config['key']) > $maxSize)
		{
			$config['key'] = substr($config['key'], 0, $maxSize);
		}
		
		$this->cipher = $config['cipher'];
		$this->key    = $config['key'];
		$this->mode   = $config['mode'];
		$this->ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);
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
		$iv = mcrypt_create_iv($this->ivSize, MCRYPT_DEV_URANDOM);
		
		return base64_encode($iv . mcrypt_encrypt($this->cipher, $this->key, $string, $this->mode, $iv));
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
		
		return rtrim(mcrypt_decrypt($this->cipher, $this->key, $string, $this->mode, $iv), "\0");
	}
}

/** -------------------- End of file -------------------- **/