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
	/**
	 * MAC length.
	 *
	 * @var int
	 */
	const MAC_LENGTH = 64;

	/**
	 * Secret used to sign and validate strings.
	 *
	 * @var string
	 */
	protected $secret;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $secret  Secret used to sign and validate strings
	 */
	public function __construct(string $secret)
	{
		$this->secret = $secret;
	}

	/**
	 * Returns the signature.
	 *
	 * @access  protected
	 * @param   string     $string  The string you want to sign
	 * @return  string
	 */
	protected function getSignature(string $string): string
	{
		return hash_hmac('sha256', $string, $this->secret);
	}

	/**
	 * Returns a signed string.
	 *
	 * @access  public
	 * @param   string  $string  The string you want to sign
	 * @return  string
	 */
	public function sign(string $string): string
	{
		return $this->getSignature($string) . $string;
	}

	/**
	 * Returns the original string if the signature is valid or FALSE if not.
	 *
	 * @access  public
	 * @param   string       $string  The string you want to validate
	 * @return  string|bool
	 */
	public function validate(string $string)
	{
		$validated = mb_substr($string, static::MAC_LENGTH, null, '8bit');

		if(hash_equals($this->getSignature($validated), mb_substr($string, 0, static::MAC_LENGTH, '8bit')))
		{
			return $validated;
		}

		return false;
	}
}