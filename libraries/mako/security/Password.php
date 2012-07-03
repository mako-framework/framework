<?php

namespace mako\security;

use \Closure;
use \mako\String;

/**
* Secure password hashing and validation.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Password
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Default computing cost.
	*
	* @var int
	*/

	const COST = 10;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Protected constructor since this is a static class.
	*
	* @access  protected
	*/

	protected function __construct()
	{
		// Nothing here
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Returns a salted BCrypt hash of the password.
	*
	* @access  public
	* @param   string  $password  Password
	* @param   int     $cost      (optional) Computing cost
	* @return  string
	*/

	public static function hash($password, $cost = Password::COST)
	{
		// Set cost

		if($cost < 4 || $cost > 31)
		{
			$cost = static::COST;
		}

		$cost = str_pad($cost, 2, '0', STR_PAD_LEFT);

		// Generate random salt

		if(function_exists('openssl_random_pseudo_bytes'))
		{
			$salt = openssl_random_pseudo_bytes(16);
		}
		else
		{
			$salt = String::random();
		}

		$salt = substr(strtr(base64_encode($salt), '+', '.'), 0, 22);

		// Return hash

		return crypt($password, '$2a$' . $cost . '$' . $salt);
	}

	/**
	* Validates a BCrypt hash.
	*
	* @access  public
	* @param   string   $password     Password
	* @param   string   $hash         Password hash
	* @param   Closure  $legacyCheck  (optional) Legacy check
	* @return  boolean
	*/

	public static function check($password, $hash, Closure $legacyCheck = null)
	{
		if(stripos($hash, '$2a$') === false)
		{
			return call_user_func($legacyCheck, $password, $hash);
		}

		return crypt($password, $hash) === $hash;
	}
}

/** -------------------- End of file --------------------**/