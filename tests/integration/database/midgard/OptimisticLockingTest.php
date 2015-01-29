<?php

namespace mako\tests\integration\database\midgard;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class OptimisticLock extends \TestORM
{
	use \mako\database\midgard\traits\OptimisticLockingTrait;

	protected $tableName = 'optimistic_locks';
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group integration
 * @group integration:database
 * @requires extension PDO
 * @requires extension pdo_sqlite
 */

class OptimisticLockingTest extends \ORMTestCase
{
	/**
	 * @expectedException \mako\database\midgard\traits\StaleRecordException
	 */

	public function testOptimisticLockUpdate()
	{
		$record1 = OptimisticLock::ascending('id')->limit(1)->first();

		$record2 = OptimisticLock::ascending('id')->limit(1)->first();

		$record1->value = 'bar';

		$this->assertTrue($record1->save());

		$record2->value = 'bar';

		$record2->save();
	}

	/**
	 * @expectedException \mako\database\midgard\traits\StaleRecordException
	 */

	public function testOptimisticLockDelete()
	{
		$record1 = OptimisticLock::ascending('id')->limit(1)->first();

		$record2 = OptimisticLock::ascending('id')->limit(1)->first();

		$record1->value = 'bar';

		$this->assertTrue($record1->save());

		$record2->delete();
	}

	/**
	 *
	 */

	public function testOptimisticLockReload()
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

	public function testOptimisticLockReloadNonExistent()
	{
		$optimisticLock = new OptimisticLock;

		$reloaded = $optimisticLock->reload();

		$this->assertFalse($reloaded);
	}

	/**
	 *
	 */

	public function testOptimisticLockInsert()
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