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

		$this->assertSame(0o000, Permission::calculate(Permission::None));

		//

		$this->assertSame(0o100, Permission::calculate(Permission::OwnerExecute));

		$this->assertSame(0o200, Permission::calculate(Permission::OwnerWrite));

		$this->assertSame(0o300, Permission::calculate(Permission::OwnerExecuteWrite));

		$this->assertSame(0o400, Permission::calculate(Permission::OwnerRead));

		$this->assertSame(0o500, Permission::calculate(Permission::OwnerExecuteRead));

		$this->assertSame(0o600, Permission::calculate(Permission::OwnerWriteRead));

		$this->assertSame(0o700, Permission::calculate(Permission::OwnerFull));

		//

		$this->assertSame(0o010, Permission::calculate(Permission::GroupExecute));

		$this->assertSame(0o020, Permission::calculate(Permission::GroupWrite));

		$this->assertSame(0o030, Permission::calculate(Permission::GroupExecuteWrite));

		$this->assertSame(0o040, Permission::calculate(Permission::GroupRead));

		$this->assertSame(0o050, Permission::calculate(Permission::GroupExecuteRead));

		$this->assertSame(0o060, Permission::calculate(Permission::GroupWriteRead));

		$this->assertSame(0o070, Permission::calculate(Permission::GroupFull));

		//

		$this->assertSame(0o001, Permission::calculate(Permission::PublicExecute));

		$this->assertSame(0o002, Permission::calculate(Permission::PublicWrite));

		$this->assertSame(0o003, Permission::calculate(Permission::PublicExecuteWrite));

		$this->assertSame(0o004, Permission::calculate(Permission::PublicRead));

		$this->assertSame(0o005, Permission::calculate(Permission::PublicExecuteRead));

		$this->assertSame(0o006, Permission::calculate(Permission::PublicWriteRead));

		$this->assertSame(0o007, Permission::calculate(Permission::PublicFull));

		//

		$this->assertSame(0o1000, Permission::calculate(Permission::SpecialSticky));

		$this->assertSame(0o2000, Permission::calculate(Permission::SpecialSetGid));

		$this->assertSame(0o4000, Permission::calculate(Permission::SpecialSetUid));

		// Test combinations

		$this->assertSame(0o666, Permission::calculate(
			Permission::OwnerWriteRead,
			Permission::GroupWriteRead,
			Permission::PublicWriteRead)
		);

		$this->assertSame(0o777, Permission::calculate(
			Permission::OwnerFull,
			Permission::GroupFull,
			Permission::PublicFull
		));

		$this->assertSame(0o744, Permission::calculate(
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::PublicRead)
		);

		$this->assertSame(0o755, Permission::calculate(
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute)
		);

		$this->assertSame(0o1755, Permission::calculate(
			Permission::SpecialSticky,
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute)
		);

		$this->assertSame(0o3755, Permission::calculate(
			Permission::SpecialSticky,
			Permission::SpecialSetGid,
			Permission::OwnerFull,
			Permission::GroupRead,
			Permission::GroupExecute,
			Permission::PublicRead,
			Permission::PublicExecute)
		);
	}

	/**
	 *
	 */
	public function testHasPermissionsWithInvalidPermissions(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessage('The integer [ 13337 ] does not represent a valid octal between 0o0000 and 0o7777.');

		Permission::hasPermissions(13337, Permission::None);
	}

	/**
	 *
	 */
	public function testHasPermissions(): void
	{
		$this->assertTrue(Permission::hasPermissions(0o666, Permission::OwnerWriteRead));

		$this->assertTrue(Permission::hasPermissions(0o777, Permission::OwnerFull));

		$this->assertTrue(Permission::hasPermissions(0o777, Permission::OwnerFull, Permission::GroupFull));

		$this->assertTrue(Permission::hasPermissions(0o777, Permission::OwnerFull, Permission::GroupFull, Permission::PublicFull));

		$this->assertTrue(Permission::hasPermissions(0o755, Permission::OwnerFull));

		$this->assertTrue(Permission::hasPermissions(0o1000, Permission::SpecialSticky));

		$this->assertTrue(Permission::hasPermissions(0o3000, Permission::SpecialSticky, Permission::SpecialSetGid));

		$this->assertTrue(Permission::hasPermissions(0o7000, Permission::SpecialSticky, Permission::SpecialSetGid, Permission::SpecialSetUid));

		$this->assertFalse(Permission::hasPermissions(0o755, Permission::GroupWrite));

		$this->assertFalse(Permission::hasPermissions(0o755, Permission::PublicWrite));
	}

	/**
	 *
	 */
	public function testHasPermissionsWithNoPermissions(): void
	{
		$this->assertTrue(Permission::hasPermissions(0o000));

		$this->assertTrue(Permission::hasPermissions(0o000, Permission::None));

		$this->assertFalse(Permission::hasPermissions(0o777, Permission::None));
	}
}
