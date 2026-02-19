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

	case NONE = 0o0000;

	// Owner permissions

	case OWNER_EXECUTE = 0o0100;
	case OWNER_WRITE = 0o0200;
	case OWNER_EXECUTE_WRITE = 0o0300;
	case OWNER_READ = 0o0400;
	case OWNER_EXECUTE_READ = 0o0500;
	case OWNER_WRITE_READ = 0o0600;
	case OWNER_FULL = 0o0700;

	// Group permissions

	case GROUP_EXECUTE = 0o0010;
	case GROUP_WRITE = 0o0020;
	case GROUP_EXECUTE_WRITE = 0o0030;
	case GROUP_READ = 0o0040;
	case GROUP_EXECUTE_READ = 0o0050;
	case GROUP_WRITE_READ = 0o0060;
	case GROUP_FULL = 0o0070;

	// Public permissions

	case PUBLIC_EXECUTE = 0o0001;
	case PUBLIC_WRITE = 0o0002;
	case PUBLIC_EXECUTE_WRITE = 0o0003;
	case PUBLIC_READ = 0o0004;
	case PUBLIC_EXECUTE_READ = 0o0005;
	case PUBLIC_WRITE_READ = 0o0006;
	case PUBLIC_FULL = 0o0007;

	// Full permissions (owner, group, and public)

	case FULL = 0o0777;

	// Special bits

	case SPECIAL_STICKY = 0o1000;
	case SPECIAL_SETGID = 0o2000;
	case SPECIAL_SETUID = 0o4000;

	// Full permissions (owner, group, and public) with all special bits

	case FULL_WITH_ALL_SPECIAL = 0o7777;

	/**
	 * Calculates sum of the specified permissions.
	 */
	public static function calculate(Permission ...$permission): int
	{
		$permissions = 0o0000;

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
		if ($permissions < 0o0000 || $permissions > 0o7777) {
			throw new InvalidArgumentException(sprintf('The integer [ %s ] does not represent a valid octal between 0o0000 and 0o7777.', $permissions));
		}

		$permission = empty($permission) ? 0o0000 : self::calculate(...$permission);

		if ($permission === 0o0000) {
			return $permissions === 0o0000;
		}

		return ($permissions & $permission) === $permission;
	}
}
