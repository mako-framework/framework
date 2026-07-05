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

	case None = 0o0000;

	// Owner permissions

	case OwnerExecute = 0o0100;
	case OwnerWrite = 0o0200;
	case OwnerExecuteWrite = 0o0300;
	case OwnerRead = 0o0400;
	case OwnerExecuteRead = 0o0500;
	case OwnerWriteRead = 0o0600;
	case OwnerFull = 0o0700;

	// Group permissions

	case GroupExecute = 0o0010;
	case GroupWrite = 0o0020;
	case GroupExecuteWrite = 0o0030;
	case GroupRead = 0o0040;
	case GroupExecuteRead = 0o0050;
	case GroupWriteRead = 0o0060;
	case GroupFull = 0o0070;

	// Public permissions

	case PublicExecute = 0o0001;
	case PublicWrite = 0o0002;
	case PublicExecuteWrite = 0o0003;
	case PublicRead = 0o0004;
	case PublicExecuteRead = 0o0005;
	case PublicWriteRead = 0o0006;
	case PublicFull = 0o0007;

	// Full permissions (owner, group, and public)

	case Full = 0o0777;

	// Special bits

	case SpecialSticky = 0o1000;
	case SpecialSetGid = 0o2000;
	case SpecialSetUid = 0o4000;

	// Full permissions (owner, group, and public) with all special bits

	case FullWithAllSpecial = 0o7777;

	/**
	 * Calculates sum of the specified permissions.
	 */
	public static function calculate(Permission ...$permission): int
	{
		$permissions = self::None->value;

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
		if ($permissions < self::None->value || $permissions > self::FullWithAllSpecial->value) {
			throw new InvalidArgumentException(sprintf('The integer [ %s ] does not represent a valid octal between 0o0000 and 0o7777.', $permissions));
		}

		$permission = empty($permission) ? self::None->value : self::calculate(...$permission);

		if ($permission === self::None->value) {
			return $permissions === self::None->value;
		}

		return ($permissions & $permission) === $permission;
	}
}
