<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security\crypto;

use \RuntimeException;

use \mako\security\crypto\adapters\AdapterInterface;
use \mako\security\Signer;

/**
 * Crypto wrapper.
 *
 * @author  Frederic G. Østby
 */

class Crypto
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Cache adapter.
	 * 
	 * @var \mako\security\crypto\adapters\AdapterInterface
	 */

	protected $adapter;

	/**
	 * Signer.
	 * 
	 * @var \mako\security\Signer
	 */

	protected $signer;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\security\crypto\adapters\AdapterInterface  $adapter  Crypto adapter
	 * @param   \mako\security\Signer                            $signer   (optional) Signer instance.
	 */

	public function __construct(AdapterInterface $adapter, Signer $signer = null)
	{
		$this->adapter = $adapter;

		$this->signer = $signer;
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
		return $this->adapter->encrypt($data);
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
		return $this->adapter->decrypt($data);
	}

	/**
	 * Encrypts and signs data.
	 * 
	 * @access  public
	 * @param   string  $data  Data to encrypt
	 * @return  string
	 */

	public function encryptAndSign($data)
	{
		if(empty($this->signer))
		{
			throw new RuntimeException(vsprintf("%s(): A [ Signer ] instance is required to sign data.", [__METHOD__]));
		}

		return $this->signer->sign($this->encrypt($data));
	}

	/**
	 * Validates and decrypts data.
	 *
	 * @access  public
	 * @param   string  $data  String to decrypt
	 * @return  string
	 */

	public function validateAndDecrypt($data)
	{
		if(empty($this->signer))
		{
			throw new RuntimeException(vsprintf("%s(): A [ Signer ] instance is required to validate signed data.", [__METHOD__]));
		}

		$data = $this->signer->validate($data);

		return ($data === false) ? false : $this->decrypt($data);
	}
}