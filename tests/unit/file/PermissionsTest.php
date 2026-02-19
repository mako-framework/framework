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

		$this->assertSame([Permission::NONE], $permissions->getPermissions());

		//

		$permissions = Permissions::fromInt(0o700);

		$this->assertSame([Permission::OWNER_READ, Permission::OWNER_WRITE, Permission::OWNER_EXECUTE], $permissions->getPermissions());

		//

		$permissions = Permissions::fromInt(0o444);

		$this->assertSame([Permission::OWNER_READ, Permission::GROUP_READ, Permission::PUBLIC_READ], $permissions->getPermissions());

		//

		$permissions = Permissions::fromInt(0o666);

		$this->assertSame([Permission::OWNER_READ, Permission::OWNER_WRITE, Permission::GROUP_READ, Permission::GROUP_WRITE, Permission::PUBLIC_READ, Permission::PUBLIC_WRITE], $permissions->getPermissions());
	}

	/**
	 *
	 */
	public function testGetPermissions(): void
	{
		$permissions = new Permissions;

		$this->assertSame([Permission::NONE], $permissions->getPermissions());

		//

		$permissions = new Permissions(Permission::OWNER_FULL);

		$this->assertSame([Permission::OWNER_READ, Permission::OWNER_WRITE, Permission::OWNER_EXECUTE], $permissions->getPermissions());
	}

	/**
	 *
	 */
	public function testHasPermissions(): void
	{
		$permissions = new Permissions;

		$this->assertTrue($permissions->hasPermissions());

		$this->assertTrue($permissions->hasPermissions(Permission::NONE));

		$this->assertFalse($permissions->hasPermissions(Permission::SPECIAL_SETUID));

		$this->assertFalse($permissions->hasPermissions(Permission::OWNER_READ));

		$this->assertFalse($permissions->hasPermissions(Permission::GROUP_READ));

		$this->assertFalse($permissions->hasPermissions(Permission::PUBLIC_READ));

		//

		$permissions = new Permissions(Permission::OWNER_READ, Permission::GROUP_READ, Permission::PUBLIC_READ);

		$this->assertTrue($permissions->hasPermissions(Permission::OWNER_READ));

		$this->assertTrue($permissions->hasPermissions(Permission::GROUP_READ));

		$this->assertTrue($permissions->hasPermissions(Permission::PUBLIC_READ));

		//

		$permissions = new Permissions(Permission::SPECIAL_STICKY, Permission::OWNER_READ, Permission::GROUP_READ, Permission::PUBLIC_READ);

		$this->assertTrue($permissions->hasPermissions(Permission::SPECIAL_STICKY));

		$this->assertTrue($permissions->hasPermissions(Permission::OWNER_READ));

		$this->assertTrue($permissions->hasPermissions(Permission::GROUP_READ));

		$this->assertTrue($permissions->hasPermissions(Permission::PUBLIC_READ));
	}

	/**
	 *
	 */
	public function testToInt(): void
	{
		$permissions = new Permissions;

		$this->assertSame(0o000, $permissions->toInt());

		//

		$permissions = new Permissions(Permission::NONE);

		$this->assertSame(0o000, $permissions->toInt());

		//

		$permissions = new Permissions(Permission::OWNER_FULL, Permission::GROUP_FULL, Permission::PUBLIC_FULL);

		$this->assertSame(0o777, $permissions->toInt());

		//

		$permissions = new Permissions(
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE
		);

		$this->assertSame(0o755, $permissions->toInt());

		//

		$permissions = new Permissions(
			Permission::SPECIAL_STICKY,
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE
		);

		$this->assertSame(0o1755, $permissions->toInt());

		//

		$permissions = new Permissions(
			Permission::SPECIAL_STICKY,
			Permission::SPECIAL_SETGID,
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE
		);

		$this->assertSame(0o3755, $permissions->toInt());

		//

		$permissions = new Permissions(
			Permission::SPECIAL_STICKY,
			Permission::SPECIAL_SETGID,
			Permission::SPECIAL_SETUID,
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE
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

		$permissions = new Permissions(Permission::NONE);

		$this->assertSame('000', $permissions->toOctalString());

		//

		$permissions = new Permissions(Permission::OWNER_FULL, Permission::GROUP_FULL, Permission::PUBLIC_FULL);

		$this->assertSame('777', $permissions->toOctalString());

		//

		$permissions = new Permissions(Permission::PUBLIC_FULL);

		$this->assertSame('007', $permissions->toOctalString());

		//

		$permissions = new Permissions(Permission::GROUP_FULL);

		$this->assertSame('070', $permissions->toOctalString());

		//

		$permissions = new Permissions(
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE
		);

		$this->assertSame('755', $permissions->toOctalString());

		//

		$permissions = new Permissions(
			Permission::SPECIAL_STICKY,
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE
		);

		$this->assertSame('1755', $permissions->toOctalString());

		//

		$permissions = new Permissions(
			Permission::SPECIAL_STICKY,
			Permission::SPECIAL_SETGID,
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE
		);

		$this->assertSame('3755', $permissions->toOctalString());

		//

		$permissions = new Permissions(
			Permission::SPECIAL_STICKY,
			Permission::SPECIAL_SETGID,
			Permission::SPECIAL_SETUID,
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE
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

		$permissions = new Permissions(Permission::NONE);

		$this->assertSame('---------', $permissions->toRwxString());

		//

		$permissions = new Permissions(Permission::OWNER_FULL, Permission::GROUP_FULL, Permission::PUBLIC_FULL);

		$this->assertSame('rwxrwxrwx', $permissions->toRwxString());

		//

		$permissions = new Permissions(Permission::PUBLIC_FULL);

		$this->assertSame('------rwx', $permissions->toRwxString());

		//

		$permissions = new Permissions(
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE
		);

		$this->assertSame('rwxr-xr-x', $permissions->toRwxString());

		//

		$permissions = new Permissions(
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE,
			Permission::SPECIAL_STICKY,
			Permission::SPECIAL_SETGID,
			Permission::SPECIAL_SETUID
		);

		$this->assertSame('rwsr-sr-t', $permissions->toRwxString());

		//

		$permissions = new Permissions(
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::PUBLIC_READ,
			Permission::SPECIAL_STICKY,
			Permission::SPECIAL_SETGID,
			Permission::SPECIAL_SETUID
		);

		$this->assertSame('rwsr-Sr-T', $permissions->toRwxString());

		//

		$permissions = new Permissions(
			Permission::OWNER_READ,
			Permission::GROUP_READ,
			Permission::PUBLIC_READ,
			Permission::SPECIAL_STICKY,
			Permission::SPECIAL_SETGID,
			Permission::SPECIAL_SETUID
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

		$this->assertInstanceOf(Permissions::class, $permissions->add(Permission::OWNER_FULL));

		$this->assertSame(0o0700, $permissions->toInt());

		//

		$permissions->add(Permission::OWNER_FULL);

		$this->assertSame(0o0700, $permissions->toInt());

		//

		$permissions->add(Permission::GROUP_FULL);

		$this->assertSame(0o0770, $permissions->toInt());

		//

		$permissions->add(Permission::PUBLIC_READ, Permission::PUBLIC_WRITE, Permission::PUBLIC_EXECUTE);

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

		$this->assertInstanceOf(Permissions::class, $permissions->remove(Permission::PUBLIC_WRITE));

		$this->assertSame(0o0775, $permissions->toInt());

		//

		$permissions->remove(Permission::PUBLIC_WRITE);

		$this->assertSame(0o0775, $permissions->toInt());

		//

		$permissions->remove(Permission::GROUP_WRITE);

		$this->assertSame(0o0755, $permissions->toInt());

		//

		$permissions->remove(Permission::PUBLIC_EXECUTE, Permission::GROUP_EXECUTE);

		$this->assertSame(0o0744, $permissions->toInt());
	}
}
