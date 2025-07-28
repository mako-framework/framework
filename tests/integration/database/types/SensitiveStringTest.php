<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\types;

use mako\database\exceptions\DatabaseException;
use mako\database\types\SensitiveString;
use mako\tests\integration\InMemoryDbTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('integration')]
#[Group('integration:database')]
#[RequiresPhpExtension('pdo')]
#[RequiresPhpExtension('pdo_sqlite')]
class SensitiveStringTest extends InMemoryDbTestCase
{
	/**
	 *
	 */
	public function testSensitiveString(): void
	{
		$email = 'foo@example.org';

		$user = $this->connectionManager->getConnection()->first(<<<'SQL'
			SELECT * FROM users WHERE email = ?
		SQL, [new SensitiveString($email)]);

		$this->assertSame($email, $user->email);
	}

	/**
	 *
	 */
	public function testSensitiveStringInLog(): void
	{
		$this->connectionManager->getConnection()->first(<<<'SQL'
			SELECT * FROM users WHERE email = ?
		SQL, [new SensitiveString('foo@example.org')]);

		$logs = $this->connectionManager->getConnection()->getLog();

		$this->assertStringContainsString('email = mako\database\types\SensitiveString', $logs[0]['query']);
	}

	/**
	 *
	 */
	public function testSensitiveStringInExceptionMessage(): void
	{
		try {
			$this->connectionManager->getConnection()->first(<<<'SQL'
				SELECT * FROM usrs WHERE email = ?
			SQL, [new SensitiveString('foo@example.org')]);
		}
		catch (DatabaseException $e) {
			$this->assertStringContainsString('email = mako\database\types\SensitiveString', $e->getMessage());
		}
	}
}
