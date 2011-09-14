<?php

namespace mako\crypto
{
	use \Exception;

	/**
	* Mcrypt cryptography adapter.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/
	
	class Mcrypt
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* The cipher method to use for encryption.
		*/
		
		protected $cipher;
		
		/**
		* Key used to encrypt/decrypt data.
		*/
		
		protected $key;
		
		/**
		* Encryption mode.
		*/
		
		protected $mode;
		
		/**
		* Initialization vector size.
		*/
		
		protected $ivSize;
		
		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------
		
		/**
		* Constructor.
		*
		* @access  public
		* @param   array   Configuration
		*/
		
		public function __construct(array $config)
		{
			if(extension_loaded('mcrypt') === false)
			{
				throw new Exception(__CLASS__ . ": Mcrypt is not available.");
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
		* Encrypts data.
		*
		* @access  public
		* @param   string  String to encrypt
		* @return  string
		*/
		
		public function encrypt($string)
		{
			$iv = mcrypt_create_iv($this->ivSize, MCRYPT_DEV_URANDOM);
			
			return base64_encode($iv . mcrypt_encrypt($this->cipher, $this->key, $string, $this->mode, $iv));
		}
		
		/**
		* Decrypts data.
		*
		* @access  public
		* @param   string  String to decrypt
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
}

/** -------------------- End of file --------------------**/