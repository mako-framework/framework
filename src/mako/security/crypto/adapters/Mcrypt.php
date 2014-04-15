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
	 * Key used to encrypt/decrypt string.
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
	 * Add PKCS #7 padding.
	 * 
	 * @access  protected
	 * @param   string     $string  String we want to pad
	 * @return  string
	 */

	protected function addPadding($string)
	{
		$blockSize = mcrypt_get_block_size($this->cipher, $this->mode);

		$pad = $blockSize - (strlen($string) % $blockSize);

		return $string . str_repeat(chr($pad), $pad);
	}

	/**
	 * Remove PKCS #7 padding.
	 * 
	 * @access  protected
	 * @param   string          $string  String we want to unpad
	 * @return  string|boolean
	 */

	protected function stripPadding($string)
	{
		$last = substr($string, -1);

		$ascii = ord($last);

		$length = strlen($string) - $ascii;

		if(substr($string, $length) === str_repeat($last, $ascii))
		{
			return substr($string, 0, $length);
		}

		return false;
	}

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
		
		return base64_encode($iv . mcrypt_encrypt($this->cipher, $this->key, $this->addPadding($string), $this->mode, $iv));
	}

	/**
	 * Decrypts string.
	 *
	 * @access  public
	 * @param   string          $string  String to decrypt
	 * @return  string|boolean
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

		return $this->stripPadding(mcrypt_decrypt($this->cipher, $this->key, $string, $this->mode, $iv));
	}
}