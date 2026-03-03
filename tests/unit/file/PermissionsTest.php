<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\file;

use InvalidArgumentException;
use mako\file\Permission;
use mako\file\Permissions;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class PermissionsTest extends TestCase
{
	/**
	 *
	 */
	public function testFromIntWithInvalidValue(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessage('The integer [ 13337 ] does not represent a valid octal between 0o0000 and 0o7777.');

		Permissions::fromInt(13337);
	}

	/**
	 *
	 */
	public function testFromInt(): void
	{
		$permissions = Permissions::fromInt(0o000);

		$this->assertSame([Permission::None], $permissions->getPermissions());

		//

		$permissions = Permissions::fromInt(0o700);

		$this->assertSame([Permission::OwnerRead, Permission::OwnerWrite, Permission::OwnerExecute], $permissions->getPermissions());

		//

		$permissions = Permissions::fromInt(0o444);

		$this->assertSame([Permission::OwnerRead, Permission::GroupRead, Permission::PublicRead], $permissions->getPermissions());

		//

		$permissions = Permissions::fromInt(0o666);

		$this->assertSame([Permission::OwnerRead, Permission::OwnerWrite, Permission::GroupRead, Permission::GroupWrite, Permission::PublicRead, Permission::PublicWrite], $permissions->getPermissions());
	}

	/**
	 *
	 */
	public function testGetPermissions(): void
	{
		$permissions = new Permissions;

		$this->assertSame([Permission::None], $permissions->getPermissions());

		//

		$permissions = new Permissions(Permission::OwnerFull);

		$this->assertSame([Permission::OwnerRead, Permission::OwnerWrite, Permission::OwnerExecute], $permissions->getPermissions());
	}

	/**
	 *
	 */
	public function testHasPermissions(): void
	{
		$permissions = new Permissions;

		$this->assertTrue($permissions->hasPermissions());

		$this->assertTrue($permissions->hasPermissions(Permission::None));

		$this->assertFalse($permissions->hasPermissions(Permission::SpecialSetUid));

		$this->assertFalse($permissions->hasPermissions(Permission::OwnerRead));

		$this->assertFalse($permissions->hasPermissions(Permission::GroupRead));

		$this->assertFalse($permissions->hasPermissions(Permission::PublicRead));

		//

		$permissions = new Permissions(Permission::OwnerRead, Permission::GroupRead, Permission::PublicRead);

		$this->assertTrue($permissions->hasPermissions(Permission::OwnerRead));

		$this->assertTrue($permissions->hasPermissions(Permission::GroupRead));

		$this->assertTrue($permissions->hasPermissions(Permission::PublicRead));

		//

		$permissions = new Permissions(Permission::SpecialSticky, Permission::OwnerRead, Permission::GroupRead, Permission::PublicRead);

		$this->assertTrue($permissions->hasPermissions(Permission::SpecialSticky));

		$this->assertTrue($permissions->hasPermissions(Permission::OwnerRead));

		$this->assertTrue($permissions->hasPermissions(Permission::GroupRead));

		$this->assertTrue($permissions->hasPermissions(Permission::PublicRead));
	}

	/**
	 *
	 */
	public function testToInt(): void
	{
		$permissions = new Permissions;

		$this->assertSame(0o000, $permissions->toInt());

		//

		$permissions = new Permissions(Permission::None);

		$this->assertSame(0o000, $permissions->toInt());

		//

		$permissions = new Permissions(Permission::OwnerFull, Permission::GroupFull, Permission::PublicFull);

		$this->assertSame(0o777, $permissions->toInt());

		//

		$permissions = new Permissions(
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute
		);

		$this->assertSame(0o755, $permissions->toInt());

		//

		$permissions = new Permissions(
			Permission::SpecialSticky,
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute
		);

		$this->assertSame(0o1755, $permissions->toInt());

		//

		$permissions = new Permissions(
			Permission::SpecialSticky,
			Permission::SpecialSetGid,
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute
		);

		$this->assertSame(0o3755, $permissions->toInt());

		//

		$permissions = new Permissions(
			Permission::SpecialSticky,
			Permission::SpecialSetGid,
			Permission::SpecialSetUid,
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute
		);

		$this->assertSame(0o7755, $permissions->toInt());
	}

	/**
	 *
	 */
	public function testToOctalString(): void
	{
		$permissions = new Permissions;

		$this->assertSame('000', $permissions->toOctalString());

		//

		$permissions = new Permissions(Permission::None);

		$this->assertSame('000', $permissions->toOctalString());

		//

		$permissions = new Permissions(Permission::OwnerFull, Permission::GroupFull, Permission::PublicFull);

		$this->assertSame('777', $permissions->toOctalString());

		//

		$permissions = new Permissions(Permission::PublicFull);

		$this->assertSame('007', $permissions->toOctalString());

		//

		$permissions = new Permissions(Permission::GroupFull);

		$this->assertSame('070', $permissions->toOctalString());

		//

		$permissions = new Permissions(
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute
		);

		$this->assertSame('755', $permissions->toOctalString());

		//

		$permissions = new Permissions(
			Permission::SpecialSticky,
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute
		);

		$this->assertSame('1755', $permissions->toOctalString());

		//

		$permissions = new Permissions(
			Permission::SpecialSticky,
			Permission::SpecialSetGid,
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute
		);

		$this->assertSame('3755', $permissions->toOctalString());

		//

		$permissions = new Permissions(
			Permission::SpecialSticky,
			Permission::SpecialSetGid,
			Permission::SpecialSetUid,
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute
		);

		$this->assertSame('7755', $permissions->toOctalString());
	}

	/**
	 *
	 */
	public function testToRwxString(): void
	{
		$permissions = new Permissions;

		$this->assertSame('---------', $permissions->toRwxString());

		//

		$permissions = new Permissions(Permission::None);

		$this->assertSame('---------', $permissions->toRwxString());

		//

		$permissions = new Permissions(Permission::OwnerFull, Permission::GroupFull, Permission::PublicFull);

		$this->assertSame('rwxrwxrwx', $permissions->toRwxString());

		//

		$permissions = new Permissions(Permission::PublicFull);

		$this->assertSame('------rwx', $permissions->toRwxString());

		//

		$permissions = new Permissions(
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute
		);

		$this->assertSame('rwxr-xr-x', $permissions->toRwxString());

		//

		$permissions = new Permissions(
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute,
			Permission::SpecialSticky,
			Permission::SpecialSetGid,
			Permission::SpecialSetUid
		);

		$this->assertSame('rwsr-sr-t', $permissions->toRwxString());

		//

		$permissions = new Permissions(
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::PublicRead,
			Permission::SpecialSticky,
			Permission::SpecialSetGid,
			Permission::SpecialSetUid
		);

		$this->assertSame('rwsr-Sr-T', $permissions->toRwxString());

		//

		$permissions = new Permissions(
			Permission::OwnerRead,
			Permission::GroupRead,
			Permission::PublicRead,
			Permission::SpecialSticky,
			Permission::SpecialSetGid,
			Permission::SpecialSetUid
		);

		$this->assertSame('r-Sr-Sr-T', $permissions->toRwxString());
	}

	/**
	 *
	 */
	public function testAdd(): void
	{
		$permissions = new Permissions;

		$this->assertSame(0o0000, $permissions->toInt());

		//

		$this->assertInstanceOf(Permissions::class, $permissions->add(Permission::OwnerFull));

		$this->assertSame(0o0700, $permissions->toInt());

		//

		$permissions->add(Permission::OwnerFull);

		$this->assertSame(0o0700, $permissions->toInt());

		//

		$permissions->add(Permission::GroupFull);

		$this->assertSame(0o0770, $permissions->toInt());

		//

		$permissions->add(Permission::PublicRead, Permission::PublicWrite, Permission::PublicExecute);

		$this->assertSame(0o0777, $permissions->toInt());
	}

	/**
	 *
	 */
	public function testRemove(): void
	{
		$permissions = Permissions::fromInt(0o777);

		$this->assertSame(0o0777, $permissions->toInt());

		//

		$this->assertInstanceOf(Permissions::class, $permissions->remove(Permission::PublicWrite));

		$this->assertSame(0o0775, $permissions->toInt());

		//

		$permissions->remove(Permission::PublicWrite);

		$this->assertSame(0o0775, $permissions->toInt());

		//

		$permissions->remove(Permission::GroupWrite);

		$this->assertSame(0o0755, $permissions->toInt());

		//

		$permissions->remove(Permission::PublicExecute, Permission::GroupExecute);

		$this->assertSame(0o0744, $permissions->toInt());
	}
}
