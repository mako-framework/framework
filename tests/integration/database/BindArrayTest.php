<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database;

use mako\tests\integration\InMemoryDbTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('integration')]
#[Group('integration:database')]
#[RequiresPhpExtension('pdo')]
#[RequiresPhpExtension('pdo_sqlite')]
class BindArrayTest extends InMemoryDbTestCase
{
	/**
	 *
	 */
	public function testBindArray(): void
	{
		$count = $this->connectionManager->getConnection()->column(<<<'SQL'
		SELECT COUNT(*) FROM "users" WHERE "id" IN ([?])
		SQL, [[1, 2, 3]]);

		$query = $this->connectionManager->getConnection()->getLog()[0]['query'];

		$this->assertSame(3, $count);
		$this->assertSame('SELECT COUNT(*) FROM "users" WHERE "id" IN (1, 2, 3)', $query);
	}
}
