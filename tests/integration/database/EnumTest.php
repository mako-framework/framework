<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database;

use mako\tests\integration\InMemoryDbTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

enum BackedUserEmailEnum: string
{
	case FOO = 'foo@example.org';
	case BAR = 'bar@example.org';
}

enum BackedUserIdEnum: int
{
	case FOO = 1;
	case BAR = 2;
}

enum UsernameEnum
{
	case foo;
	case bar;
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('integration')]
#[Group('integration:database')]
#[RequiresPhpExtension('pdo')]
#[RequiresPhpExtension('pdo_sqlite')]
class EnumTest extends InMemoryDbTestCase
{
	/**
	 *
	 */
	public function testBackedStringEnum(): void
	{
		$user = $this->connectionManager->getConnection()->first(<<<'SQL'
			SELECT * FROM users WHERE email = ?
		SQL, [BackedUserEmailEnum::FOO]);

		$this->assertSame(BackedUserEmailEnum::FOO->value, $user->email);

		$user = $this->connectionManager->getConnection()->first(<<<'SQL'
			SELECT * FROM users WHERE email = ?
		SQL, [BackedUserEmailEnum::BAR]);

		$this->assertSame(BackedUserEmailEnum::BAR->value, $user->email);
	}

	/**
	 *
	 */
	public function testBackedIntEnum(): void
	{
		$user = $this->connectionManager->getConnection()->first(<<<'SQL'
			SELECT * FROM users WHERE id = ?
		SQL, [BackedUserIdEnum::FOO]);

		$this->assertSame(BackedUserIdEnum::FOO->value, $user->id);

		$user = $this->connectionManager->getConnection()->first(<<<'SQL'
			SELECT * FROM users WHERE id = ?
		SQL, [BackedUserIdEnum::BAR]);

		$this->assertSame(BackedUserIdEnum::BAR->value, $user->id);
	}

	/**
	 *
	 */
	public function testEnum(): void
	{
		$user = $this->connectionManager->getConnection()->first(<<<'SQL'
			SELECT * FROM users WHERE username = ?
		SQL, [UsernameEnum::foo]);

		$this->assertSame(UsernameEnum::foo->name, $user->username);

		$user = $this->connectionManager->getConnection()->first(<<<'SQL'
			SELECT * FROM users WHERE username = ?
		SQL, [UsernameEnum::bar]);

		$this->assertSame(UsernameEnum::bar->name, $user->username);
	}
}
