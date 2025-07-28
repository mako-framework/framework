<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database;

use mako\tests\integration\InMemoryDbTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Stringable;

#[Group('integration')]
#[Group('integration:database')]
#[RequiresPhpExtension('pdo')]
#[RequiresPhpExtension('pdo_sqlite')]
class StringableTest extends InMemoryDbTestCase
{
	/**
	 *
	 */
	public function testStringable(): void
	{
		$stringable = new class implements Stringable {
			public string $email = 'foo@example.org';

			public function __toString(): string
			{
				return $this->email;
			}
		};

		$user = $this->connectionManager->getConnection()->first(<<<'SQL'
			SELECT * FROM users WHERE email = ?
		SQL, [$stringable]);

		$this->assertSame($stringable->email, $user->email);
	}
}
