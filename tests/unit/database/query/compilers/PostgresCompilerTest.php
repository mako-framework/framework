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
use mako\database\query\Subquery;
use mako\database\query\values\in\Vector as InVector;
use mako\database\query\values\out\Vector as OutVector;
use mako\database\query\values\out\VectorDistance as OutVectorDistance;
use mako\database\query\VectorDistance;
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
	public function testBasicCosineWhereVectorDistance(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->whereVectorDistance('embedding', [1, 2, 3, 4, 5], maxDistance: 0.5)
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "embedding" <=> ? <= ?', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]', 0.5], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicEuclidianWhereVectorDistance(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->whereVectorDistance('embedding', [1, 2, 3, 4, 5], maxDistance: 0.5, vectorDistance: VectorDistance::EUCLIDEAN)
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "embedding" <-> ? <= ?', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]', 0.5], $query['params']);
	}

	/**
	 *
	 */
	public function testCosineWhereVectorDistanceStringVector(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->whereVectorDistance('embedding', '[1,2,3,4,5]', maxDistance: 0.5)
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "embedding" <=> ? <= ?', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]', 0.5], $query['params']);
	}

	/**
	 *
	 */
	public function testCosineWhereVectorDistanceFromSubquery(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->whereVectorDistance('embedding', new Subquery(function (Query $query): void {
			$query->table('embeddings')->select(['embedding'])->where('id', '=', 1);
		}), maxDistance: 0.5)
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "embedding" <=> (SELECT "embedding" FROM "embeddings" WHERE "id" = ?) <= ?', $query['sql']);
		$this->assertEquals([1, 0.5], $query['params']);
	}

	/**
	 *
	 */
	public function testOrderByVectorDistanceCosine(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->orderByVectorDistance('embedding', [1, 2, 3, 4, 5])
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "embedding" <=> ? ASC', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]'], $query['params']);
	}

	/**
	 *
	 */
	public function testOrderByVectorDistanceCosineWithStringVector(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->orderByVectorDistance('embedding', '[1,2,3,4,5]')
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "embedding" <=> ? ASC', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]'], $query['params']);
	}

	/**
	 *
	 */
	public function testOrderByVectorDistanceCosineWithSubqueryVector(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->orderByVectorDistance('embedding', new Subquery(function (Query $query): void {
			$query->table('embeddings')->select(['embedding'])->where('id', '=', 1);
		}))
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "embedding" <=> (SELECT "embedding" FROM "embeddings" WHERE "id" = ?) ASC', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testOrderByVectorDistanceEuclidean(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->orderByVectorDistance('embedding', [1, 2, 3, 4, 5], VectorDistance::EUCLIDEAN)
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "embedding" <-> ? ASC', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]'], $query['params']);
	}

	/**
	 *
	 */
	public function testAscendingVectorDistance(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->ascendingVectorDistance('embedding', [1, 2, 3, 4, 5])
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "embedding" <=> ? ASC', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]'], $query['params']);
	}

	/**
	 *
	 */
	public function testDescendingVectorDistance(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->descendingVectorDistance('embedding', [1, 2, 3, 4, 5])
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "embedding" <=> ? DESC', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]'], $query['params']);
	}

	/**
	 *
	 */
	public function testVectorSelectValue(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->select([new OutVector('embedding')])
		->getCompiler()->select();

		$this->assertEquals('SELECT "embedding" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testVectorSelectValueWithAlias(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->select([new OutVector('embedding')->as('vector')])
		->getCompiler()->select();

		$this->assertEquals('SELECT "embedding" AS "vector" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testVectorCosineDistanceSelectValue(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->select([new OutVectorDistance('embedding', [1, 2, 3, 4])])
		->getCompiler()->select();

		$this->assertEquals('SELECT "embedding" <=> ? FROM "foobar"', $query['sql']);
		$this->assertEquals(['[1,2,3,4]'], $query['params']);
	}

	/**
	 *
	 */
	public function testVectorEuclideanDistanceSelectValue(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->select([new OutVectorDistance('embedding', [1, 2, 3, 4], VectorDistance::EUCLIDEAN)])
		->getCompiler()->select();

		$this->assertEquals('SELECT "embedding" <-> ? FROM "foobar"', $query['sql']);
		$this->assertEquals(['[1,2,3,4]'], $query['params']);
	}

	/**
	 *
	 */
	public function testVectorDistanceSelectValueWithAlias(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->select([new OutVectorDistance('embedding', [1, 2, 3, 4])->as('distance')])
		->getCompiler()->select();

		$this->assertEquals('SELECT "embedding" <=> ? AS "distance" FROM "foobar"', $query['sql']);
		$this->assertEquals(['[1,2,3,4]'], $query['params']);
	}

	/**
	 *
	 */
	public function testVectorInsertValue(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->getCompiler()->insert(['embedding' => new InVector([1, 2, 3])]);

		$this->assertEquals('INSERT INTO "foobar" ("embedding") VALUES (?)', $query['sql']);
		$this->assertEquals(['[1,2,3]'], $query['params']);
	}

	/**
	 *
	 */
	public function testStringVectorInsertValue(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->getCompiler()->insert(['embedding' => new InVector('[1,2,3]')]);

		$this->assertEquals('INSERT INTO "foobar" ("embedding") VALUES (?)', $query['sql']);
		$this->assertEquals(['[1,2,3]'], $query['params']);
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
