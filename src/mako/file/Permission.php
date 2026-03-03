<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\file;

use Deprecated;
use InvalidArgumentException;

use function sprintf;

/**
 * File permissions.
 */
enum Permission: int
{
	/* Start compatibility */
	#[Deprecated('use Permission::None instead', 'Mako 12.2.0')]
	public const NONE = self::None;
	#[Deprecated('use Permission::OwnerExecute instead', 'Mako 12.2.0')]
	public const OWNER_EXECUTE = self::OwnerExecute;
	#[Deprecated('use Permission::OwnerWrite instead', 'Mako 12.2.0')]
	public const OWNER_WRITE = self::OwnerWrite;
	#[Deprecated('use Permission::OwnerExecuteWrite instead', 'Mako 12.2.0')]
	public const OWNER_EXECUTE_WRITE = self::OwnerExecuteWrite;
	#[Deprecated('use Permission::OwnerRead instead', 'Mako 12.2.0')]
	public const OWNER_READ = self::OwnerRead;
	#[Deprecated('use Permission::OwnerExecuteRead instead', 'Mako 12.2.0')]
	public const OWNER_EXECUTE_READ = self::OwnerExecuteRead;
	#[Deprecated('use Permission::OwnerWriteRead instead', 'Mako 12.2.0')]
	public const OWNER_WRITE_READ = self::OwnerWriteRead;
	#[Deprecated('use Permission::OwnerFull instead', 'Mako 12.2.0')]
	public const OWNER_FULL = self::OwnerFull;
	#[Deprecated('use Permission::GroupExecute instead', 'Mako 12.2.0')]
	public const GROUP_EXECUTE = self::GroupExecute;
	#[Deprecated('use Permission::GroupWrite instead', 'Mako 12.2.0')]
	public const GROUP_WRITE = self::GroupWrite;
	#[Deprecated('use Permission::GroupExecuteWrite instead', 'Mako 12.2.0')]
	public const GROUP_EXECUTE_WRITE = self::GroupExecuteWrite;
	#[Deprecated('use Permission::GroupRead instead', 'Mako 12.2.0')]
	public const GROUP_READ = self::GroupRead;
	#[Deprecated('use Permission::GroupExecuteRead instead', 'Mako 12.2.0')]
	public const GROUP_EXECUTE_READ = self::GroupExecuteRead;
	#[Deprecated('use Permission::GroupWriteRead instead', 'Mako 12.2.0')]
	public const GROUP_WRITE_READ = self::GroupWriteRead;
	#[Deprecated('use Permission::GroupFull instead', 'Mako 12.2.0')]
	public const GROUP_FULL = self::GroupFull;
	#[Deprecated('use Permission::PublicExecute instead', 'Mako 12.2.0')]
	public const PUBLIC_EXECUTE = self::PublicExecute;
	#[Deprecated('use Permission::PublicWrite instead', 'Mako 12.2.0')]
	public const PUBLIC_WRITE = self::PublicWrite;
	#[Deprecated('use Permission::PublicExecuteWrite instead', 'Mako 12.2.0')]
	public const PUBLIC_EXECUTE_WRITE = self::PublicExecuteWrite;
	#[Deprecated('use Permission::PublicRead instead', 'Mako 12.2.0')]
	public const PUBLIC_READ = self::PublicRead;
	#[Deprecated('use Permission::PublicExecuteRead instead', 'Mako 12.2.0')]
	public const PUBLIC_EXECUTE_READ = self::PublicExecuteRead;
	#[Deprecated('use Permission::PublicWriteRead instead', 'Mako 12.2.0')]
	public const PUBLIC_WRITE_READ = self::PublicWriteRead;
	#[Deprecated('use Permission::PublicFull instead', 'Mako 12.2.0')]
	public const PUBLIC_FULL = self::PublicFull;
	#[Deprecated('use Permission::Full instead', 'Mako 12.2.0')]
	public const FULL = self::Full;
	#[Deprecated('use Permission::SpecialSticky instead', 'Mako 12.2.0')]
	public const SPECIAL_STICKY = self::SpecialSticky;
	#[Deprecated('use Permission::SpecialSetGid instead', 'Mako 12.2.0')]
	public const SPECIAL_SETGID = self::SpecialSetGid;
	#[Deprecated('use Permission::SpecialSetUid instead', 'Mako 12.2.0')]
	public const SPECIAL_SETUID = self::SpecialSetUid;
	#[Deprecated('use Permission::FullWithAllSpecial instead', 'Mako 12.2.0')]
	public const FULL_WITH_ALL_SPECIAL = self::FullWithAllSpecial;
	/* End compatibility */

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
