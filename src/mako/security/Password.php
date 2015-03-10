<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security;

/**
 * Secure password hashing and validation.
 *
 * @author  Frederic G. Østby
 */

class Password
{
	/**
	 * Default computing costs.
	 *
	 * @var array
	 */

	protected static $costs =
	[
		PASSWORD_BCRYPT => 10,
	];

	/**
	 * Normalizes the cost value.
	 *
	 * @access  protected
	 * @param   int        $cost  Computing cost
	 * @return  int
	 */

	protected static function normalizeCost($cost)
	{
		switch(PASSWORD_DEFAULT)
		{
			case PASSWORD_BCRYPT:
				$cost = ($cost < 4 || $cost > 31) ? static::$costs[PASSWORD_BCRYPT] : $cost;
				break;
		}

		return $cost;
	}

	/**
	 * Set default computing cost.
	 *
	 * @access  public
	 * @param   int     $cost  Computing cost
	 */

	public static function setDefaultComputingCost($cost)
	{
		static::$costs[PASSWORD_DEFAULT] = static::normalizeCost($cost);
	}

	/**
	 * Get default computing cost.
	 *
	 * @access  public
	 * @return  int
	 */

	public static function getDefaultComputingCost()
	{
		return static::$costs[PASSWORD_DEFAULT];
	}

	/**
	 * Returns a password hash.
	 *
	 * @access  public
	 * @param   string    $password  Password
	 * @param   null|int  $cost      Computing cost
	 * @return  string
	 */

	public static function hash($password, $cost = null)
	{
		$cost = static::normalizeCost($cost ?: static::$costs[PASSWORD_DEFAULT]);

		return password_hash($password, PASSWORD_DEFAULT, ['cost' => $cost]);
	}

	/**
	 * Checks if the password needs to be rehashed.
	 *
	 * @access  public
	 * @param   string    $hash  Password hash to check
	 * @param   null|int  $cost  Computing cost
	 * @return  boolean
	 */

	public static function needsRehash($hash, $cost = null)
	{
		$cost = static::normalizeCost($cost ?: static::$costs[PASSWORD_DEFAULT]);

		return password_needs_rehash($hash, PASSWORD_DEFAULT, ['cost' => $cost]);
	}

	/**
	 * Validates a password hash.
	 *
	 * @access  public
	 * @param   string    $password  Password
	 * @param   string    $hash      Password hash
	 * @return  boolean
	 */

	public static function validate($password, $hash)
	{
		return password_verify($password, $hash);
	}
}