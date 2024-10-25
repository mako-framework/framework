<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database;

use mako\database\connections\Connection;
use mako\database\query\compilers\Compiler;
use mako\database\query\helpers\Helper;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('integration')]
#[Group('integration:database')]
#[RequiresPhpExtension('pdo')]
#[RequiresPhpExtension('pdo_sqlite')]
class ConnectionTest extends TestCase
{
	/**
	 *
	 */
	public function testClone(): void
	{
		$connection = new Connection('test', Compiler::class, Helper::class, ['dsn' => 'sqlite::memory:']);

		$connectionClone = clone $connection;

		$this->assertSame('test', $connection->getName());

		$this->assertSame('test_clone', $connectionClone->getName());

		$this->assertNotSame(spl_object_id($connection->getPDO()), spl_object_id($connectionClone->getPDO()));
	}
}
