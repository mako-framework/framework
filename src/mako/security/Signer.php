<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security;

/**
 * Signs and validates strings using MACs (message authentication codes).
 *
 * @author  Frederic G. Østby
 */

class Signer
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * MAC length.
	 * 
	 * @var int
	 */

	const MAC_LENGTH = 40;
	
	/**
	 * Secret used to sign and validate strings.
	 * 
	 * @var string
	 */

	protected $secret;
	
	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $secret  Secret used to sign and validate strings
	 */

	public function __construct($secret)
	{
		$this->secret = $secret;
	}
	
	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the signature.
	 * 
	 * @access  protected
	 * @param   string     $string  The string you want to sign
	 * @return  string
	 */

	protected function getSignature($string)
	{
		return hash_hmac('sha1', $string, $this->secret);
	}
	
	/**
	 * Returns a signed string.
	 * 
	 * @access  public
	 * @param   string  $string  The string you want to sign
	 * @return  string
	 */

	public function sign($string)
	{
		return $this->getSignature($string) . $string;
	}

	/**
	 * Returns the original string if the signature is valid or FALSE if not.
	 * 
	 * @access  public
	 * @param   string  $string  The string you want to validate
	 * @return  mixed
	 */

	public function validate($string)
	{
		$validated = substr($string, static::MAC_LENGTH);

		if($this->getSignature($validated) === substr($string, 0, static::MAC_LENGTH))
		{
			return $validated;
		}

		return false;
	}
}