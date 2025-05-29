<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use mako\database\connections\Firebird as FirebirdConnection;
use mako\database\query\compilers\Firebird as FirebirdCompiler;
use mako\database\query\helpers\HelperInterface;
use mako\database\query\Query;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class FirebirdCompilerTest extends TestCase
{
	/**
	 * @return FirebirdConnection|Mockery\MockInterface
	 */
	protected function getConnection()
	{
		/** @var FirebirdConnection|Mockery\MockInterface $connection */
		$connection = Mockery::mock(FirebirdConnection::class);

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock(HelperInterface::class));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function ($query) {
			return new FirebirdCompiler($query);
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

		$this->assertEquals('SELECT 1, 2, 3 FROM RDB$DATABASE', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" ROWS 1 TO 10', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" ROWS 11 TO 20', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" FOR UPDATE WITH LOCK', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" WITH LOCK', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" WITH LOCK', $query['sql']);
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
	public function testBetweenDate(): void
	{
		$query = $this->getBuilder();

		$query->betweenDate('date', '2019-07-05', '2019-07-06');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE CAST("date" AS DATE) BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05', '2019-07-06'], $query['params']);
	}

	/**
	 *
	 */
	public function testOrBetweenDate(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->orBetweenDate('date', '2019-07-05', '2019-07-06');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? OR CAST("date" AS DATE) BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['bar', '2019-07-05', '2019-07-06'], $query['params']);
	}

	/**
	 *
	 */
	public function testNotBetweenDate(): void
	{
		$query = $this->getBuilder();

		$query->notBetweenDate('date', '2019-07-05', '2019-07-06');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE CAST("date" AS DATE) NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05', '2019-07-06'], $query['params']);
	}

	/**
	 *
	 */
	public function testOrNotBetweenDate(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->orNotBetweenDate('date', '2019-07-05', '2019-07-06');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? OR CAST("date" AS DATE) NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['bar', '2019-07-05', '2019-07-06'], $query['params']);
	}

	/**
	 *
	 */
	public function testWhereDate(): void
	{
		$query = $this->getBuilder();

		$query->whereDate('date', '=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE CAST("date" AS DATE) = ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '!=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE CAST("date" AS DATE) != ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<>', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE CAST("date" AS DATE) <> ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '>', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE CAST("date" AS DATE) > ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '>=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE CAST("date" AS DATE) >= ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE CAST("date" AS DATE) < ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE CAST("date" AS DATE) <= ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', 'LIKE', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE CAST("date" AS DATE) LIKE ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);
	}

	/**
	 *
	 */
	public function testOrWhereDate(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->orWhereDate('date', '=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? OR CAST("date" AS DATE) = ?', $query['sql']);
		$this->assertEquals(['bar', '2019-07-05'], $query['params']);
	}

	/**
	 *
	 */
	public function testInsertAndReturn(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->insertAndReturn(['foo' => 'bar'], ['id', 'foo']);

		$this->assertEquals('INSERT INTO "foobar" ("foo") VALUES (?) RETURNING "id", "foo"', $query['sql']);
		$this->assertEquals(['bar'], $query['params']);
	}

	/**
	 *
	 */
	public function testInsertMultipleAndReturn(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->insertMultipleAndReturn(['id', 'foo'], ['foo' => 'bar'], ['bar' => 'baz']);

		$this->assertEquals('INSERT INTO "foobar" ("foo") VALUES (?), (?) RETURNING "id", "foo"', $query['sql']);
		$this->assertEquals(['bar', 'baz'], $query['params']);
	}

	/**
	 *
	 */
	public function testUpdateAndReturn(): void
	{
		$query = $this->getBuilder();

		$query->where('id', '=', 1);

		$query = $query->getCompiler()->updateAndReturn(['foo' => 'bar'], ['id', 'foo']);

		$this->assertEquals('UPDATE "foobar" SET "foo" = ? WHERE "id" = ? RETURNING "id", "foo"', $query['sql']);
		$this->assertEquals(['bar', 1], $query['params']);
	}
}
