<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security;

use function password_hash;
use function password_needs_rehash;
use function password_verify;

/**
 * Secure password hashing and validation.
 *
 * @author Frederic G. Østby
 */
class Password
{
	/**
	 * Default computing options.
	 *
	 * @var array
	 */
	protected static $options =
	[
		PASSWORD_BCRYPT => ['cost' => PASSWORD_BCRYPT_DEFAULT_COST],
	];

	/**
	 * Normalizes the computing options.
	 *
	 * @param  array $options Computing options
	 * @return array
	 */
	protected static function normalizeOptions(array $options): array
	{
		switch(PASSWORD_DEFAULT)
		{
			case PASSWORD_BCRYPT:
				$options['cost'] = ($options['cost'] < 4 || $options['cost'] > 31) ? static::$options[PASSWORD_BCRYPT]['cost'] : $options['cost'];
				break;
		}

		return $options;
	}

	/**
	 * Set default computing cost.
	 *
	 * @param array $options Computing cost
	 */
	public static function setDefaultComputingOptions(array $options)
	{
		static::$options[PASSWORD_DEFAULT] = static::normalizeOptions($options);
	}

	/**
	 * Get default computing options.
	 *
	 * @return array
	 */
	public static function getDefaultComputingOptions(): array
	{
		return static::$options[PASSWORD_DEFAULT];
	}

	/**
	 * Returns a password hash.
	 *
	 * @param  string     $password Password
	 * @param  array|null $options  Computing options
	 * @return string
	 */
	public static function hash(string $password, array $options = null): string
	{
		$options = ($options === null) ? static::$options[PASSWORD_DEFAULT] : static::normalizeOptions($options);

		return password_hash($password, PASSWORD_DEFAULT, $options);
	}

	/**
	 * Checks if the password needs to be rehashed.
	 *
	 * @param  string     $hash    Password hash to check
	 * @param  array|null $options Computing options
	 * @return bool
	 */
	public static function needsRehash(string $hash, array $options = null): bool
	{
		$options = ($options === null) ? static::$options[PASSWORD_DEFAULT] : static::normalizeOptions($options);

		return password_needs_rehash($hash, PASSWORD_DEFAULT, $options);
	}

	/**
	 * Validates a password hash.
	 *
	 * @param  string $password Password
	 * @param  string $hash     Password hash
	 * @return bool
	 */
	public static function validate(string $password, string $hash): bool
	{
		return password_verify($password, $hash);
	}
}
