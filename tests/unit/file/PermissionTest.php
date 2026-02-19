<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\file;

use InvalidArgumentException;
use mako\file\Permission;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class PermissionTest extends TestCase
{
	/**
	 *
	 */
	public function testCalculate(): void
	{
		// Test individual permissions

		$this->assertSame(0o000, Permission::calculate());

		$this->assertSame(0o000, Permission::calculate(Permission::NONE));

		//

		$this->assertSame(0o100, Permission::calculate(Permission::OWNER_EXECUTE));

		$this->assertSame(0o200, Permission::calculate(Permission::OWNER_WRITE));

		$this->assertSame(0o300, Permission::calculate(Permission::OWNER_EXECUTE_WRITE));

		$this->assertSame(0o400, Permission::calculate(Permission::OWNER_READ));

		$this->assertSame(0o500, Permission::calculate(Permission::OWNER_EXECUTE_READ));

		$this->assertSame(0o600, Permission::calculate(Permission::OWNER_WRITE_READ));

		$this->assertSame(0o700, Permission::calculate(Permission::OWNER_FULL));

		//

		$this->assertSame(0o010, Permission::calculate(Permission::GROUP_EXECUTE));

		$this->assertSame(0o020, Permission::calculate(Permission::GROUP_WRITE));

		$this->assertSame(0o030, Permission::calculate(Permission::GROUP_EXECUTE_WRITE));

		$this->assertSame(0o040, Permission::calculate(Permission::GROUP_READ));

		$this->assertSame(0o050, Permission::calculate(Permission::GROUP_EXECUTE_READ));

		$this->assertSame(0o060, Permission::calculate(Permission::GROUP_WRITE_READ));

		$this->assertSame(0o070, Permission::calculate(Permission::GROUP_FULL));

		//

		$this->assertSame(0o001, Permission::calculate(Permission::PUBLIC_EXECUTE));

		$this->assertSame(0o002, Permission::calculate(Permission::PUBLIC_WRITE));

		$this->assertSame(0o003, Permission::calculate(Permission::PUBLIC_EXECUTE_WRITE));

		$this->assertSame(0o004, Permission::calculate(Permission::PUBLIC_READ));

		$this->assertSame(0o005, Permission::calculate(Permission::PUBLIC_EXECUTE_READ));

		$this->assertSame(0o006, Permission::calculate(Permission::PUBLIC_WRITE_READ));

		$this->assertSame(0o007, Permission::calculate(Permission::PUBLIC_FULL));

		//

		$this->assertSame(0o1000, Permission::calculate(Permission::SPECIAL_STICKY));

		$this->assertSame(0o2000, Permission::calculate(Permission::SPECIAL_SETGID));

		$this->assertSame(0o4000, Permission::calculate(Permission::SPECIAL_SETUID));

		// Test combinations

		$this->assertSame(0o666, Permission::calculate(
			Permission::OWNER_WRITE_READ,
			Permission::GROUP_WRITE_READ,
			Permission::PUBLIC_WRITE_READ)
		);

		$this->assertSame(0o777, Permission::calculate(
			Permission::OWNER_FULL,
			Permission::GROUP_FULL,
			Permission::PUBLIC_FULL
		));

		$this->assertSame(0o744, Permission::calculate(
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::PUBLIC_READ)
		);

		$this->assertSame(0o755, Permission::calculate(
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE)
		);

		$this->assertSame(0o1755, Permission::calculate(
			Permission::SPECIAL_STICKY,
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE)
		);

		$this->assertSame(0o3755, Permission::calculate(
			Permission::SPECIAL_STICKY,
			Permission::SPECIAL_SETGID,
			Permission::OWNER_FULL,
			Permission::GROUP_READ,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_EXECUTE)
		);
	}

	/**
	 *
	 */
	public function testHasPermissionsWithInvalidPermissions(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessage('The integer [ 13337 ] does not represent a valid octal between 0o0000 and 0o7777.');

		Permission::hasPermissions(13337, Permission::NONE);
	}

	/**
	 *
	 */
	public function testHasPermissions(): void
	{
		$this->assertTrue(Permission::hasPermissions(0o666, Permission::OWNER_WRITE_READ));

		$this->assertTrue(Permission::hasPermissions(0o777, Permission::OWNER_FULL));

		$this->assertTrue(Permission::hasPermissions(0o777, Permission::OWNER_FULL, Permission::GROUP_FULL));

		$this->assertTrue(Permission::hasPermissions(0o777, Permission::OWNER_FULL, Permission::GROUP_FULL, Permission::PUBLIC_FULL));

		$this->assertTrue(Permission::hasPermissions(0o755, Permission::OWNER_FULL));

		$this->assertTrue(Permission::hasPermissions(0o1000, Permission::SPECIAL_STICKY));

		$this->assertTrue(Permission::hasPermissions(0o3000, Permission::SPECIAL_STICKY, Permission::SPECIAL_SETGID));

		$this->assertTrue(Permission::hasPermissions(0o7000, Permission::SPECIAL_STICKY, Permission::SPECIAL_SETGID, Permission::SPECIAL_SETUID));

		$this->assertFalse(Permission::hasPermissions(0o755, Permission::GROUP_WRITE));

		$this->assertFalse(Permission::hasPermissions(0o755, Permission::PUBLIC_WRITE));
	}

	/**
	 *
	 */
	public function testHasPermissionsWithNoPermissions(): void
	{
		$this->assertTrue(Permission::hasPermissions(0o000));

		$this->assertTrue(Permission::hasPermissions(0o000, Permission::NONE));

		$this->assertFalse(Permission::hasPermissions(0o777, Permission::NONE));
	}
}
