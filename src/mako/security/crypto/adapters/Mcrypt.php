<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security\crypto\adapters;

/**
 * MCrypt adapter.
 *
 * @author  Frederic G. Østby
 */

class MCrypt implements \mako\security\crypto\adapters\AdapterInterface
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
	 * @param   string  $key     Encryption key
	 * @param   int     $cipher  (optional) Cipher
	 * @param   int     $mode    (optional) Mode
	 */

	public function __construct($key, $cipher = null, $mode = null)
	{
		$this->key = $key;

		$this->cipher = $cipher ?: MCRYPT_RIJNDAEL_256;

		$this->mode = $mode ?: MCRYPT_MODE_ECB;

		$maxSize = mcrypt_get_key_size($this->cipher, $this->mode);
		
		if(mb_strlen($this->key) > $maxSize)
		{
			$this->key = substr($this->key, 0, $maxSize);
		}

		$this->ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);
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
		$iv = mcrypt_create_iv($this->ivSize, MCRYPT_DEV_URANDOM);
		
		return base64_encode($iv . mcrypt_encrypt($this->cipher, $this->key, $data, $this->mode, $iv));
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

		$data = mcrypt_decrypt($this->cipher, $this->key, $data, $this->mode, $iv);

		if($data === false)
		{
			return false;
		}
		
		return rtrim($data, "\0");
	}
}

