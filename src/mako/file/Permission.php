<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\file;

use InvalidArgumentException;

use function sprintf;

/**
 * File permissions.
 */
enum Permission: int
{
	// No permissions

	case NONE = 0o000;

	// Owner permissions

	case OWNER_EXECUTE = 0o100;
	case OWNER_WRITE = 0o200;
	case OWNER_EXECUTE_WRITE = 0o300;
	case OWNER_READ = 0o400;
	case OWNER_EXECUTE_READ = 0o500;
	case OWNER_WRITE_READ = 0o600;
	case OWNER_FULL = 0o700;

	// Group permissions

	case GROUP_EXECUTE = 0o010;
	case GROUP_WRITE = 0o020;
	case GROUP_EXECUTE_WRITE = 0o030;
	case GROUP_READ = 0o040;
	case GROUP_EXECUTE_READ = 0o050;
	case GROUP_WRITE_READ = 0o060;
	case GROUP_FULL = 0o070;

	// Public permissions

	case PUBLIC_EXECUTE = 0o001;
	case PUBLIC_WRITE = 0o002;
	case PUBLIC_EXECUTE_WRITE = 0o003;
	case PUBLIC_READ = 0o004;
	case PUBLIC_EXECUTE_READ = 0o005;
	case PUBLIC_WRITE_READ = 0o006;
	case PUBLIC_FULL = 0o007;

	// Full permissions (owner, group, and public)

	case FULL = 0o777;

	/**
	 * Calculates sum of the specified permissions.
	 */
	public static function calculate(Permission ...$permission): int
	{
		$permissions = 0o000;

		foreach ($permission as $_permission) {
			$permissions |= $_permission->value;
		}

		return $permissions;
	}

	/**
	 * Returns TRUE if the permissions contain the specified permissions and FALSE if not.
	 */
	public static function hasPermissions(int $permissions, Permission ...$permission): bool
	{
		if ($permissions < 0o000 || $permissions > 0o777) {
			throw new InvalidArgumentException(sprintf('The integer [ %s ] does not represent a valid octal between 0o000 and 0o777.', $permissions));
		}

		$permission = empty($permission) ? 0o000 : self::calculate(...$permission);

		if ($permission === 0o000) {
			return $permissions === 0o000;
		}

		return ($permissions & $permission) === $permission;
	}
}
