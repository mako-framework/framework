<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security;

use \Closure;

/**
 * Secure password hashing and validation.
 *
 * @author  Frederic G. Østby
 */

class Password
{
	/**
	 * Default computing cost.
	 *
	 * @var int
	 */

	const COST = 10;

	/**
	 * Protected constructor since this is a static class.
	 *
	 * @access  protected
	 */

	protected function __construct()
	{
		// Nothing here
	}

	/**
	 * Checks if a hash is generated using something other than bcrypt.
	 *
	 * @access  public
	 * @param   string   $hash  Hash to check
	 * @return  boolean
	 */

	public static function isLegacyHash($hash)
	{
		return stripos($hash, '$2y$') !== 0;
	}

	/**
	 * Returns a bcrypt hash of the password.
	 *
	 * @access  public
	 * @param   string  $password  Password
	 * @param   int     $cost      Computing cost
	 * @return  string
	 */

	public static function hash($password, $cost = Password::COST)
	{
		// Set cost

		if($cost < 4 || $cost > 31)
		{
			$cost = static::COST;
		}

		// Return hash

		return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
	}

	/**
	 * Validates a password hash.
	 *
	 * @access  public
	 * @param   string    $password     Password
	 * @param   string    $hash         Password hash
	 * @param   \Closure  $legacyCheck  Legacy check
	 * @return  boolean
	 */

	public static function validate($password, $hash, Closure $legacyCheck = null)
	{
		if($legacyCheck !== null && static::isLegacyHash($hash))
		{
			return $legacyCheck($password, $hash);
		}

		return password_verify($password, $hash);
	}
}