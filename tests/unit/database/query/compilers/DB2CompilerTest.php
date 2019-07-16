<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use mako\database\query\Query;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class DB2CompilerTest extends TestCase
{
	/**
	 *
	 */
	protected function getConnection()
	{
		$connection = Mockery::mock('\mako\database\connections\Connection');

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock('\mako\database\query\helpers\HelperInterface'));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function($query)
		{
			return new \mako\database\query\compilers\DB2($query);
		});

		return $connection;
	}

	/**
	 *
	 */
	protected function getBuilder($table = 'foobar')
	{
		return (new Query($this->getConnection()))->table($table);
	}

	/**
	 *
	 */
	public function testBasicSelectWithoutTable(): void
	{
		$query = $this->getBuilder(null);

		$query = $query->selectRaw('1, 2, 3')->getCompiler()->select();

		$this->assertEquals('SELECT 1, 2, 3 FROM SYSIBM.SYSDUMMY1', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNoLimit(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);

		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimit(): void
	{
		$query = $this->getBuilder();

		$query->limit(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS mako_rownum FROM "foobar") AS mako1 WHERE mako_rownum BETWEEN 1 AND 10', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimitAndOffset(): void
	{
		$query = $this->getBuilder();

		$query->limit(10);
		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS mako_rownum FROM "foobar") AS mako1 WHERE mako_rownum BETWEEN 11 AND 20', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithExclusiveLock(): void
	{
		$query = $this->getBuilder();

		$query->lock();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" FOR UPDATE WITH RS', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithSharedLock(): void
	{
		$query = $this->getBuilder();

		$query->lock(false);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" FOR READ ONLY WITH RS', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithSharedLockMethod(): void
	{
		$query = $this->getBuilder();

		$query->sharedLock();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" FOR READ ONLY WITH RS', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithCustomLock(): void
	{
		$query = $this->getBuilder();

		$query->lock('CUSTOM LOCK');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" CUSTOM LOCK', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimitAndExclusiveLock(): void
	{
		$query = $this->getBuilder();

		$query->limit(10);

		$query->lock();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS mako_rownum FROM "foobar") AS mako1 WHERE mako_rownum BETWEEN 1 AND 10 FOR UPDATE WITH RS', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithPrefix(): void
	{
		$query = $this->getBuilder();

		$query->prefix('/*PREFIX*/');

		$query = $query->getCompiler()->select();

		$this->assertEquals('/*PREFIX*/ SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}
}
