<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use mako\database\connections\Postgres as PostgresConnection;
use mako\database\query\compilers\Postgres as PostgresCompiler;
use mako\database\query\helpers\HelperInterface;
use mako\database\query\Query;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class PostgresCompilerTest extends TestCase
{
	/**
	 *
	 */
	protected function getConnection(): MockInterface&PostgresConnection
	{
		$connection = Mockery::mock(PostgresConnection::class);

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock(HelperInterface::class));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function ($query) {
			return new PostgresCompiler($query);
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
	public function testSelectWithJSONColumn(): void
	{
		$query = $this->getBuilder();

		$query->select(['json->foo']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "json"->>\'foo\' FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "json"->\'foo\'->0->>\'bar\' FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->\'bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "json"->\'foo\'->0->>\'\'\'bar\' FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->"bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "json"->\'foo\'->0->>\'"bar\' FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->bar as jsonvalue']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "json"->\'foo\'->0->>\'bar\' AS "jsonvalue" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['foobar.json->foo->0->bar as jsonvalue']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "foobar"."json"->\'foo\'->0->>\'bar\' AS "jsonvalue" FROM "foobar"', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" FOR UPDATE', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" FOR SHARE', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" FOR SHARE', $query['sql']);
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
	public function testUpdateWithJSONColumn(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->update(['data->foo->bar->0' => 1]);

		$this->assertEquals('UPDATE "foobar" SET "data" = JSONB_SET("data", \'{foo,bar,0}\', \'?\')', $query['sql']);
		$this->assertEquals([1], $query['params']);

		//

		$query = $this->getBuilder();

		$query = $query->getCompiler()->update(['data->foo->"bar->0' => 1]);

		$this->assertEquals('UPDATE "foobar" SET "data" = JSONB_SET("data", \'{foo,"bar,0}\', \'?\')', $query['sql']);
		$this->assertEquals([1], $query['params']);

		//

		$query = $this->getBuilder();

		$query = $query->getCompiler()->update(['data->foo->\'bar->0' => 1]);

		$this->assertEquals('UPDATE "foobar" SET "data" = JSONB_SET("data", \'{foo,\'\'bar,0}\', \'?\')', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testBetweenDate(): void
	{
		$query = $this->getBuilder();

		$query->betweenDate('date', '2019-07-05', '2019-07-06');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000', '2019-07-06 23:59:59.999999'], $query['params']);

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

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? OR "date" BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['bar', '2019-07-05 00:00:00.000000', '2019-07-06 23:59:59.999999'], $query['params']);
	}

	/**
	 *
	 */
	public function testNotBetweenDate(): void
	{
		$query = $this->getBuilder();

		$query->notBetweenDate('date', '2019-07-05', '2019-07-06');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000', '2019-07-06 23:59:59.999999'], $query['params']);

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

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? OR "date" NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['bar', '2019-07-05 00:00:00.000000', '2019-07-06 23:59:59.999999'], $query['params']);
	}

	/**
	 *
	 */
	public function testWhereDate(): void
	{
		$query = $this->getBuilder();

		$query->whereDate('date', '=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000', '2019-07-05 23:59:59.999999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '!=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000', '2019-07-05 23:59:59.999999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<>', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000', '2019-07-05 23:59:59.999999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '>', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" > ?', $query['sql']);
		$this->assertEquals(['2019-07-05 23:59:59.999999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '>=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" >= ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" < ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" <= ?', $query['sql']);
		$this->assertEquals(['2019-07-05 23:59:59.999999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', 'LIKE', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date"::date::char(10) LIKE ?', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? OR "date" BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['bar', '2019-07-05 00:00:00.000000', '2019-07-05 23:59:59.999999'], $query['params']);
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
	public function testInsertOrUpdate(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->insertOrUpdate(['foo' => 'bar'], ['foo' => 'dupe'], ['foo']);

		$this->assertEquals('INSERT INTO "foobar" ("foo") VALUES (?) ON CONFLICT ("foo") DO UPDATE SET "foo" = ?', $query['sql']);
		$this->assertEquals(['bar', 'dupe'], $query['params']);
	}

	/**
	 *
	 */
	public function testInsertOrUpdateWithMultipleConstraints(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->insertOrUpdate(['foo' => 'bar'], ['foo' => 'dupe'], ['foo', 'bar']);

		$this->assertEquals('INSERT INTO "foobar" ("foo") VALUES (?) ON CONFLICT ("foo", "bar") DO UPDATE SET "foo" = ?', $query['sql']);
		$this->assertEquals(['bar', 'dupe'], $query['params']);
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
