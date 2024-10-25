<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard;

use mako\database\midgard\traits\exceptions\StaleRecordException;
use mako\database\midgard\traits\OptimisticLockingTrait;
use mako\tests\integration\ORMTestCase;
use mako\tests\integration\TestORM;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class OptimisticLock extends TestORM
{
	use OptimisticLockingTrait;

	protected string $tableName = 'optimistic_locks';
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('integration')]
#[Group('integration:database')]
#[RequiresPhpExtension('pdo')]
#[RequiresPhpExtension('pdo_sqlite')]
class OptimisticLockingTest extends ORMTestCase
{
	/**
	 *
	 */
	public function testOptimisticLockUpdate(): void
	{
		$this->expectException(StaleRecordException::class);

		$record1 = (new OptimisticLock)->ascending('id')->limit(1)->first();

		$record2 = (new OptimisticLock)->ascending('id')->limit(1)->first();

		$record1->value = 'bar';

		$this->assertTrue($record1->save());

		$record2->value = 'bar';

		$record2->save();
	}

	/**
	 *
	 */
	public function testOptimisticLockDelete(): void
	{
		$this->expectException(StaleRecordException::class);

		$record1 = (new OptimisticLock)->ascending('id')->limit(1)->first();

		$record2 = (new OptimisticLock)->ascending('id')->limit(1)->first();

		$record1->value = 'bar';

		$this->assertTrue($record1->save());

		$record2->delete();
	}

	/**
	 *
	 */
	public function testOptimisticLockReload(): void
	{
		$optimisticLock = OptimisticLock::get(1);

		$optimisticLock->value = 'bax';

		$this->assertEquals('bax', $optimisticLock->value);

		$reloaded = $optimisticLock->reload();

		$this->assertTrue($reloaded);

		$this->assertEquals('foo', $optimisticLock->value);
	}

	/**
	 *
	 */
	public function testOptimisticLockReloadNonExistent(): void
	{
		$optimisticLock = new OptimisticLock;

		$reloaded = $optimisticLock->reload();

		$this->assertFalse($reloaded);
	}

	/**
	 *
	 */
	public function testOptimisticLockInsert(): void
	{
		$optimisticLock = new OptimisticLock;

		$optimisticLock->value = 'hello';

		$optimisticLock->save();

		$this->assertEquals(1, $optimisticLock->getLockVersion());

		$optimisticLock->value = 'world';

		$optimisticLock->save();

		$this->assertEquals(2, $optimisticLock->getLockVersion());
	}
}
