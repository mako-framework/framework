<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use mako\database\connections\SQLite;
use mako\database\query\helpers\HelperInterface;
use mako\database\query\Query;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class SQLiteCompilerTest extends TestCase
{
	/**
	 * @return Mockery\MockInterface|SQLite
	 */
	protected function getConnection()
	{
		/** @var Mockery\MockInterface|SQLite $connection */
		$connection = Mockery::mock(SQLite::class);

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock(HelperInterface::class));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function ($query) {
			return new \mako\database\query\compilers\SQLite($query);
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
	public function testSelectWithLimitAndOffset(): void
	{
		$query = $this->getBuilder();

		$query->limit(10);
		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" LIMIT 10 OFFSET 10', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOffset(): void
	{
		$query = $this->getBuilder();

		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" LIMIT -1 OFFSET 10', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithJSONColumn(): void
	{
		$query = $this->getBuilder();

		$query->select(['json->foo->0->bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_EXTRACT("json", \'$."foo"[0]."bar"\') FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->\'bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_EXTRACT("json", \'$."foo"[0]."\'\'bar"\') FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->bar as jsonvalue']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_EXTRACT("json", \'$."foo"[0]."bar"\') AS "jsonvalue" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['foobar.json->foo->0->bar as jsonvalue']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_EXTRACT("foobar"."json", \'$."foo"[0]."bar"\') AS "jsonvalue" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testUpdateWithJSONColumn(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->update(['data->foo->bar->0' => 1]);

		$this->assertEquals('UPDATE "foobar" SET "data" = JSON_SET("data", \'$."foo"."bar"[0]\', JSON(?))', $query['sql']);
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
		$this->assertEquals(['2019-07-05 00:00:00.000', '2019-07-06 23:59:59.999'], $query['params']);
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
		$this->assertEquals(['bar', '2019-07-05 00:00:00.000', '2019-07-06 23:59:59.999'], $query['params']);
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
		$this->assertEquals(['2019-07-05 00:00:00.000', '2019-07-06 23:59:59.999'], $query['params']);
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
		$this->assertEquals(['bar', '2019-07-05 00:00:00.000', '2019-07-06 23:59:59.999'], $query['params']);
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
		$this->assertEquals(['2019-07-05 00:00:00.000', '2019-07-05 23:59:59.999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '!=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000', '2019-07-05 23:59:59.999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<>', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000', '2019-07-05 23:59:59.999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '>', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" > ?', $query['sql']);
		$this->assertEquals(['2019-07-05 23:59:59.999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '>=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" >= ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" < ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "date" <= ?', $query['sql']);
		$this->assertEquals(['2019-07-05 23:59:59.999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', 'LIKE', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE strftime(\'%Y-%m-%d\', "date") LIKE ?', $query['sql']);
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
		$this->assertEquals(['bar', '2019-07-05 00:00:00.000', '2019-07-05 23:59:59.999'], $query['params']);
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
