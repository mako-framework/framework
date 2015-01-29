<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security\crypto;

use RuntimeException;

use mako\security\crypto\encrypters\EncrypterInterface;
use mako\security\Signer;

/**
 * Crypto wrapper.
 *
 * @author  Frederic G. Østby
 */

class Crypto
{
	/**
	 * Cache adapter.
	 *
	 * @var \mako\security\crypto\encrypters\EncrypterInterface
	 */

	protected $adapter;

	/**
	 * Signer.
	 *
	 * @var \mako\security\Signer
	 */

	protected $signer;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\security\crypto\encrypters\EncrypterInterface  $adapter  Crypto adapter
	 * @param   \mako\security\Signer                                $signer   Signer instance.
	 */

	public function __construct(EncrypterInterface $adapter, Signer $signer = null)
	{
		$this->adapter = $adapter;

		$this->signer = $signer;
	}

	/**
	 * Sets the signer instance.
	 *
	 * @access  public
	 * @param   \mako\security\Signer  $signer  Signer instance
	 */

	public function setSigner(Signer $signer)
	{
		$this->signer = $signer;
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
		return $this->adapter->encrypt($string);
	}

	/**
	 * Decrypts string.
	 *
	 * @access  public
	 * @param   string         $string  String to decrypt
	 * @return  string|boolean
	 */

	public function decrypt($string)
	{
		return $this->adapter->decrypt($string);
	}

	/**
	 * Encrypts and signs string.
	 *
	 * @access  public
	 * @param   string  $string  String to encrypt
	 * @return  string
	 */

	public function encryptAndSign($string)
	{
		if(empty($this->signer))
		{
			throw new RuntimeException(vsprintf("%s(): A [ Signer ] instance is required to sign string.", [__METHOD__]));
		}

		return $this->signer->sign($this->encrypt($string));
	}

	/**
	 * Validates and decrypts string.
	 *
	 * @access  public
	 * @param   string          $string  String to decrypt
	 * @return  string|boolean
	 */

	public function validateAndDecrypt($string)
	{
		if(empty($this->signer))
		{
			throw new RuntimeException(vsprintf("%s(): A [ Signer ] instance is required to validate signed string.", [__METHOD__]));
		}

		$string = $this->signer->validate($string);

		return ($string === false) ? false : $this->decrypt($string);
	}
}