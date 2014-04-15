<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security\crypto\adapters;

use \mako\security\crypto\padders\PadderInterface;

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
	 * Key used to encrypt/decrypt string.
	 *
	 * @var string
	 */
	
	protected $key;

	/**
	 * Padder instance.
	 * 
	 * @var \mako\security\crypto\padders\PadderInterface
	 */

	protected $padder;

	/**
	 * The cipher method to use for encryption.
	 *
	 * @var int
	 */
	
	protected $cipher;
	
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
	 * @param   string                                         $key     Encryption key
	 * @param   \mako\security\crypto\padders\PadderInterface  $padder  Padder instance
	 * @param   int                                            $cipher  (optional) Cipher
	 * @param   int                                            $mode    (optional) Mode
	 */

	public function __construct($key, PadderInterface $padder, $cipher = null, $mode = null)
	{
		$this->key = $key;

		$this->padder = $padder;

		$this->cipher = $cipher ?: MCRYPT_RIJNDAEL_256;

		$this->mode = $mode ?: MCRYPT_MODE_ECB;

		$maxSize = mcrypt_get_key_size($this->cipher, $this->mode);
		
		if(strlen($this->key) > $maxSize)
		{
			$this->key = substr($this->key, 0, $maxSize);
		}

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
		$blockSize = mcrypt_get_block_size($this->cipher, $this->mode);

		$iv = mcrypt_create_iv($this->ivSize, MCRYPT_DEV_URANDOM);
		
		return base64_encode($iv . mcrypt_encrypt($this->cipher, $this->key, $this->padder->addPadding($string, $blockSize), $this->mode, $iv));
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

		return $this->padder->stripPadding(mcrypt_decrypt($this->cipher, $this->key, $string, $this->mode, $iv));
	}
}