<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard;

use mako\database\midgard\ORM;
use mako\database\midgard\traits\SensitiveStringTrait;
use mako\tests\integration\ORMTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('integration')]
#[Group('integration:database')]
#[RequiresPhpExtension('pdo')]
#[RequiresPhpExtension('pdo_sqlite')]
class SensitiveStringTest extends ORMTestCase
{
	/**
	 * Returns a ORM instance.
	 *
	 * @return ORM
	 */
	protected function getSensitiveString(): ORM
	{
		return new class extends ORM {
			use SensitiveStringTrait;

			protected string $tableName = 'users';

			protected array $sensitiveStrings = ['email'];
		};
	}

	/**
	 *
	 */
	public function testUpdate(): void
	{
		$sensitiveString = $this->getSensitiveString();

		$updated = $sensitiveString->where('id', '=', '1')->update(['email' => 'foo@example.org']);

		$this->assertSame(1, $updated);

		$this->assertSame(<<<'SQL'
		UPDATE "users" SET "email" = mako\database\types\SensitiveString WHERE "id" = '1'
		SQL, $this->getSensitiveString()->getConnection()->getLog()[0]['query']);
	}

}
