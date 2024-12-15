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

		$this->assertSame(0o400, Permission::calculate(Permission::OWNER_READ));

		$this->assertSame(0o200, Permission::calculate(Permission::OWNER_WRITE));

		$this->assertSame(0o100, Permission::calculate(Permission::OWNER_EXECUTE));

		$this->assertSame(0o700, Permission::calculate(Permission::OWNER_FULL));

		$this->assertSame(0o040, Permission::calculate(Permission::GROUP_READ));

		$this->assertSame(0o020, Permission::calculate(Permission::GROUP_WRITE));

		$this->assertSame(0o010, Permission::calculate(Permission::GROUP_EXECUTE));

		$this->assertSame(0o070, Permission::calculate(Permission::GROUP_FULL));

		$this->assertSame(0o004, Permission::calculate(Permission::PUBLIC_READ));

		$this->assertSame(0o002, Permission::calculate(Permission::PUBLIC_WRITE));

		$this->assertSame(0o001, Permission::calculate(Permission::PUBLIC_EXECUTE));

		$this->assertSame(0o007, Permission::calculate(Permission::PUBLIC_FULL));

		// Test combinations

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
	}
}
