<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\database\Database;
use mako\database\query\Query;
use mako\database\query\Raw;
use mako\database\query\Subquery;

/**
 * @group unit
 */
class BaseCompilerTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 *
	 */
	protected function getConnection()
	{
		$connection = Mockery::mock('\mako\database\connections\Connection');

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock('\mako\database\query\helpers\HelperInterface'));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function($query)
		{
			return new \mako\database\query\compilers\Compiler($query);
		});

		$connection->shouldReceive('column')->andReturn(null);

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
	public function testBasicSelect()
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicSelectWithClosure()
	{
		$query = $this->getBuilder(function($query)
		{
			$query->table('foobar');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM (SELECT * FROM "foobar") AS "mako0"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicSelectWithSubquery()
	{
		$query = $this->getBuilder(new Subquery(function($query)
		{
			$query->table('foobar');
		}));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM (SELECT * FROM "foobar")', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicSelectWithSubqueryWithTableAlias()
	{
		$query = $this->getBuilder(new Subquery(function($query)
		{
			$query->table('foobar');
		}, 'table_alias'));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM (SELECT * FROM "foobar") AS "table_alias"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testDistinctSelect()
	{
		$query = $this->getBuilder();

		$query->distinct();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT DISTINCT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithCloumns()
	{
		$query = $this->getBuilder();

		$query->select(['foo', 'bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "foo", "bar" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithCloumnAlias()
	{
		$query = $this->getBuilder();

		$query->select(['foo', 'bar as baz']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "foo", "bar" AS "baz" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithTablePrefix()
	{
		$query = $this->getBuilder();

		$query->select(['foo', 'foobar.bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "foo", "foobar"."bar" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage mako\database\query\compilers\Compiler::buildJsonGet(): The [ mako\database\query\compilers\Compiler ] query compiler does not support the unified JSON field syntax.
	 */
	public function testSelectWithJSONColumn()
	{
		$query = $this->getBuilder();

		$query->select(['json->0->bar']);

		$query = $query->getCompiler()->select();
	}

	/**
	 *
	 */
	public function testSelectWithSubqueryColumn()
	{
		$query = $this->getBuilder();

		$query->select(['foo', new Subquery(function($query)
		{
			$query->table('barfoo')->select(['baz'])->limit(1);
		}, 'baz')]);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "foo", (SELECT "baz" FROM "barfoo" LIMIT 1) AS "baz" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimit()
	{
		$query = $this->getBuilder();

		$query->limit(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" LIMIT 10', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimitAndOffset()
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
	public function testSelectWithExclusiveLock()
	{
		$query = $this->getBuilder();

		$query->lock();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithSharedLock()
	{
		$query = $this->getBuilder();

		$query->lock(false);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithCustomLock()
	{
		$query = $this->getBuilder();

		$query->lock('CUSTOM LOCK');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithWhere()
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ?', $query['sql']);
		$this->assertEquals(['bar'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithRawWhere()
	{
		$query = $this->getBuilder();

		$query->where(new Raw('LOWER("foo")'), '=', 'bar');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE LOWER("foo") = ?', $query['sql']);
		$this->assertEquals(['bar'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithWhereRaw()
	{
		$query = $this->getBuilder();

		$query->whereRaw('foo', '=', 'SUBSTRING("foo", 1, 2)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = SUBSTRING("foo", 1, 2)', $query['sql']);
		$this->assertEmpty($query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithWheres()
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->where('bar', '=', 'foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? AND "bar" = ?', $query['sql']);
		$this->assertEquals(['bar', 'foo'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrWhere()
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->orWhere('foo', '=', 'baz');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? OR "foo" = ?', $query['sql']);
		$this->assertEquals(['bar', 'baz'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrWhereRaw()
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->orWhereRaw('foo', '=', 'SUBSTRING("foo", 1, 2)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? OR "foo" = SUBSTRING("foo", 1, 2)', $query['sql']);
		$this->assertEquals(['bar'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNestedWheres()
	{
		$query = $this->getBuilder();

		$query->where(function($query)
		{
			$query->where('foo', '=', 'bar');
			$query->where('bar', '=', 'foo');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE ("foo" = ? AND "bar" = ?)', $query['sql']);
		$this->assertEquals(['bar', 'foo'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithBetween()
	{
		$query = $this->getBuilder();

		$query->between('foo', 1, 10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals([1, 10], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithBetweenAndOrBetween()
	{
		$query = $this->getBuilder();

		$query->between('foo', 1, 10);

		$query->orBetween('foo', 21, 30);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" BETWEEN ? AND ? OR "foo" BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals([1, 10, 21, 30], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNotBetween()
	{
		$query = $this->getBuilder();

		$query->notBetween('foo', 1, 10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals([1, 10], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNotBetweenAndOrNotBetween()
	{
		$query = $this->getBuilder();

		$query->notBetween('foo', 1, 10);

		$query->orNotBetween('foo', 21, 30);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" NOT BETWEEN ? AND ? OR "foo" NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals([1, 10, 21, 30], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithInAndOrIn()
	{
		$query = $this->getBuilder();

		$query->in('foo', [1, 2, 3]);

		$query->orIn('foo', [4, 5, 6]);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IN (?, ?, ?) OR "foo" IN (?, ?, ?)', $query['sql']);
		$this->assertEquals([1, 2, 3, 4, 5, 6], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithRawIn()
	{
		$query = $this->getBuilder();

		$query->in('foo', new Raw("SELECT id FROM barfoo"));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IN (SELECT id FROM barfoo)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithRawWithBoundParameters()
	{
		$query = $this->getBuilder();

		$query->where('id', '>', 1);

		$query->in('foo', new Raw("SELECT id FROM barfoo WHERE id > ?", [2]));

		$query->where('id', '<', 3);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "id" > ? AND "foo" IN (SELECT id FROM barfoo WHERE id > ?) AND "id" < ?', $query['sql']);
		$this->assertEquals([1, 2, 3], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithClosureIn()
	{
		$query = $this->getBuilder();

		$query->in('foo', function($query)
		{
			$query->table('barfoo')->select(['id']);
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IN (SELECT "id" FROM "barfoo")', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNotIn()
	{
		$query = $this->getBuilder();

		$query->notIn('foo', [1, 2, 3]);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" NOT IN (?, ?, ?)', $query['sql']);
		$this->assertEquals([1, 2, 3], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNotInAndOrNotIn()
	{
		$query = $this->getBuilder();

		$query->notIn('foo', [1, 2, 3]);

		$query->orNotIn('foo', [4, 5, 6]);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" NOT IN (?, ?, ?) OR "foo" NOT IN (?, ?, ?)', $query['sql']);
		$this->assertEquals([1, 2, 3, 4, 5, 6], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithIsNull()
	{
		$query = $this->getBuilder();

		$query->isNull('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IS NULL', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithIsNullAndOrNull()
	{
		$query = $this->getBuilder();

		$query->isNull('foo');

		$query->orIsNull('bar');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IS NULL OR "bar" IS NULL', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithIsNotNull()
	{
		$query = $this->getBuilder();

		$query->isNotNull('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IS NOT NULL', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithIsNotNullAndOrNotNull()
	{
		$query = $this->getBuilder();

		$query->isNotNull('foo');

		$query->orIsNotNull('bar');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IS NOT NULL OR "bar" IS NOT NULL', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWhereConvenienceMethods()
	{
		$query = $this->getBuilder();

		$query->eq('foo', 'bar');

		$query->notEq('foo', 'bar');

		$query->lt('foo', 'bar');

		$query->lte('foo', 'bar');

		$query->gt('foo', 'bar');

		$query->gte('foo', 'bar');

		$query->like('foo', 'bar');

		$query->notLike('foo', 'bar');

		//

		$query->eqRaw('foo', 'bar');

		$query->notEqRaw('foo', 'bar');

		$query->ltRaw('foo', 'bar');

		$query->lteRaw('foo', 'bar');

		$query->gtRaw('foo', 'bar');

		$query->gteRaw('foo', 'bar');

		$query->likeRAw('foo', 'bar');

		$query->notLikeRaw('foo', 'bar');

		//

		$query->orEq('foo', 'bar');

		$query->orNotEq('foo', 'bar');

		$query->orLt('foo', 'bar');

		$query->orLte('foo', 'bar');

		$query->orGt('foo', 'bar');

		$query->orGte('foo', 'bar');

		$query->orLike('foo', 'bar');

		$query->orNotLike('foo', 'bar');

		//

		$query->orEqRaw('foo', 'bar');

		$query->orNotEqRaw('foo', 'bar');

		$query->orLtRaw('foo', 'bar');

		$query->orLteRaw('foo', 'bar');

		$query->orGtRaw('foo', 'bar');

		$query->orGteRaw('foo', 'bar');

		$query->orLikeRaw('foo', 'bar');

		$query->orNotLikeRaw('foo', 'bar');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" = ? AND "foo" <> ? AND "foo" < ? AND "foo" <= ? AND "foo" > ? AND "foo" >= ? AND "foo" LIKE ? AND "foo" NOT LIKE ? AND "foo" = bar AND "foo" <> bar AND "foo" < bar AND "foo" <= bar AND "foo" > bar AND "foo" >= bar AND "foo" LIKE bar AND "foo" NOT LIKE bar OR "foo" = ? OR "foo" <> ? OR "foo" < ? OR "foo" <= ? OR "foo" > ? OR "foo" >= ? OR "foo" LIKE ? OR "foo" NOT LIKE ? OR "foo" = bar OR "foo" <> bar OR "foo" < bar OR "foo" <= bar OR "foo" > bar OR "foo" >= bar OR "foo" LIKE bar OR "foo" NOT LIKE bar', $query['sql']);
		$this->assertEquals(['bar', 'bar', 'bar', 'bar', 'bar', 'bar', 'bar', 'bar', 'bar', 'bar', 'bar', 'bar', 'bar', 'bar', 'bar', 'bar'], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithExistsSubquery()
	{
		$query = $this->getBuilder();

		$query->exists(new Subquery($this->getBuilder('barfoo')->where('barfoo.foobar_id', '=', new Raw('foobar.id'))));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = foobar.id)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithExistsSubqueryAndOrExists()
	{
		$query = $this->getBuilder();

		$query->exists(new Subquery($this->getBuilder('barfoo')->where('barfoo.foobar_id', '=', new Raw('foobar.id'))));

		$query->orExists(new Subquery($this->getBuilder('barfoo')->where('barfoo.foobar_id', '=', new Raw('barbaz.id'))));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = foobar.id) OR EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = barbaz.id)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithExistsClosure()
	{
		$query = $this->getBuilder();

		$query->exists(function($query)
		{
			$query->table('barfoo')->where('barfoo.foobar_id', '=', new Raw('foobar.id'));
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = foobar.id)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNotExists()
	{
		$query = $this->getBuilder();

		$query->notExists(new Subquery($this->getBuilder('barfoo')->where('barfoo.foobar_id', '=', new Raw('foobar.id'))));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE NOT EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = foobar.id)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithNotExistsAndOrNotExists()
	{
		$query = $this->getBuilder();

		$query->notExists(new Subquery($this->getBuilder('barfoo')->where('barfoo.foobar_id', '=', new Raw('foobar.id'))));

		$query->orNotExists(new Subquery($this->getBuilder('barfoo')->where('barfoo.foobar_id', '=', new Raw('barbaz.id'))));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE NOT EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = foobar.id) OR NOT EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = barbaz.id)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithJoin()
	{
		$query = $this->getBuilder();

		$query->join('barfoo', 'barfoo.foobar_id', '=', 'foobar.id');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN "barfoo" ON "barfoo"."foobar_id" = "foobar"."id"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithJoinRaw()
	{
		$query = $this->getBuilder();

		$query->joinRaw('barfoo', 'barfoo.foobar_id', '=', 'SUBSTRING("foo", 1, 2)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN "barfoo" ON "barfoo"."foobar_id" = SUBSTRING("foo", 1, 2)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLeftJoin()
	{
		$query = $this->getBuilder();

		$query->leftJoin('barfoo', 'barfoo.foobar_id', '=', 'foobar.id');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" LEFT OUTER JOIN "barfoo" ON "barfoo"."foobar_id" = "foobar"."id"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLeftJoinRaw()
	{
		$query = $this->getBuilder();

		$query->leftJoinRaw('barfoo', 'barfoo.foobar_id', '=', 'SUBSTRING("foo", 1, 2)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" LEFT OUTER JOIN "barfoo" ON "barfoo"."foobar_id" = SUBSTRING("foo", 1, 2)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithComplexJoin()
	{
		$query = $this->getBuilder();

		$query->join('barfoo', function($join)
		{
			$join->on('barfoo.foobar_id', '=', 'foobar.id');
			$join->orOn('barfoo.foobar_id', '!=', 'foobar.id');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN "barfoo" ON "barfoo"."foobar_id" = "foobar"."id" OR "barfoo"."foobar_id" != "foobar"."id"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithComplexNestedJoin()
	{
		$query = $this->getBuilder();

		$query->join('barfoo', function($join)
		{
			$join->on('barfoo.foobar_id', '=', 'foobar.id');
			$join->on(function($join)
			{
				$join->on('barfoo.foobar_id', '=', 'foobar.id');
				$join->orOn('barfoo.foobar_id', '!=', 'foobar.id');
			});
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN "barfoo" ON "barfoo"."foobar_id" = "foobar"."id" AND ("barfoo"."foobar_id" = "foobar"."id" OR "barfoo"."foobar_id" != "foobar"."id")', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithComplexRawJoin()
	{
		$query = $this->getBuilder();

		$query->join('barfoo', function($join)
		{
			$join->onRaw('barfoo.foobar_id', '=', 'SUBSTRING("foo", 1, 2)');
			$join->orOnRaw('barfoo.foobar_id', '!=', 'SUBSTRING("foo", 1, 2)');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN "barfoo" ON "barfoo"."foobar_id" = SUBSTRING("foo", 1, 2) OR "barfoo"."foobar_id" != SUBSTRING("foo", 1, 2)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithSubqueryJoin()
	{
		$query = $this->getBuilder();

		$query->join(new Subquery(function($query)
		{
			$query->table('barfoo')->where('id', '>', 1);
		}, 'barfoo'), 'barfoo.foobar_id', '=', 'foobar.id');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN (SELECT * FROM "barfoo" WHERE "id" > ?) AS "barfoo" ON "barfoo"."foobar_id" = "foobar"."id"', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithRawSubqueryJoin()
	{
		$query = $this->getBuilder();

		$query->join(new Raw('(SELECT * FROM "barfoo" WHERE "id" > 1) AS "barfoo"'), 'barfoo.foobar_id', '=', 'foobar.id');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" INNER JOIN (SELECT * FROM "barfoo" WHERE "id" > 1) AS "barfoo" ON "barfoo"."foobar_id" = "foobar"."id"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithGroupBy()
	{
		$query = $this->getBuilder('orders');

		$query->select(['customer', new Raw('SUM(price) as sum')]);

		$query->groupBy('customer');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithGroupByArray()
	{
		$query = $this->getBuilder('orders');

		$query->select(['customer', 'order_date', new Raw('SUM(price) as sum')]);

		$query->groupBy(['customer', 'order_date']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", "order_date", SUM(price) as sum FROM "orders" GROUP BY "customer", "order_date"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithHaving()
	{
		$query = $this->getBuilder('orders');

		$query->select(['customer', new Raw('SUM(price) as sum')]);

		$query->groupBy('customer');
		$query->having(new Raw('SUM(price)'), '<', 2000);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer" HAVING SUM(price) < ?', $query['sql']);
		$this->assertEquals([2000], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithHavingRaw()
	{
		$query = $this->getBuilder('orders');

		$query->select(['customer', new Raw('SUM(price) as sum')]);

		$query->groupBy('customer');
		$query->havingRaw('SUM(price)', '<', 2000);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer" HAVING SUM(price) < ?', $query['sql']);
		$this->assertEquals([2000], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithHavingAndOrHaving()
	{
		$query = $this->getBuilder('orders');

		$query->select(['customer', new Raw('SUM(price) as sum')]);

		$query->groupBy('customer');
		$query->having(new Raw('SUM(price)'), '<', 2000);
		$query->orHaving(new Raw('SUM(price)'), '>', 2000);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer" HAVING SUM(price) < ? OR SUM(price) > ?', $query['sql']);
		$this->assertEquals([2000, 2000], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithHavinRawgAndOrHavingRaw()
	{
		$query = $this->getBuilder('orders');

		$query->select(['customer', new Raw('SUM(price) as sum')]);

		$query->groupBy('customer');
		$query->havingRaw('SUM(price)', '<', 2000);
		$query->orHavingRaw('SUM(price)', '>', 2000);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer" HAVING SUM(price) < ? OR SUM(price) > ?', $query['sql']);
		$this->assertEquals([2000, 2000], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrder()
	{
		$query = $this->getBuilder();

		$query->orderBy('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo" ASC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderArray()
	{
		$query = $this->getBuilder();

		$query->orderBy(['foo', 'bar'], 'DESC');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo", "bar" DESC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderRaw()
	{
		$query = $this->getBuilder();

		$query->orderByRaw('FIELD(id, 1, 2, 3)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY FIELD(id, 1, 2, 3) ASC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderDescending()
	{
		$query = $this->getBuilder();

		$query->descending('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo" DESC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderDescendingRaw()
	{
		$query = $this->getBuilder();

		$query->descendingRaw('FIELD(id, 1, 2, 3)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY FIELD(id, 1, 2, 3) DESC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderAscending()
	{
		$query = $this->getBuilder();

		$query->ascending('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo" ASC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOrderAscendingRaw()
	{
		$query = $this->getBuilder();

		$query->ascendingRaw('FIELD(id, 1, 2, 3)');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY FIELD(id, 1, 2, 3) ASC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithMultipleOrder()
	{
		$query = $this->getBuilder();

		$query->orderBy('foo');
		$query->orderBy('bar', 'DESC');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo" ASC, "bar" DESC', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectClearOrdering()
	{
		$query = $this->getBuilder();

		$query->orderBy('foo');
		$query->orderBy('bar', 'DESC');

		$query = $query->clearOrdering()->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicDelete()
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->delete();

		$this->assertEquals('DELETE FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testDeleteWithWhere()
	{
		$query = $this->getBuilder();

		$query->where('id', '=', 1);

		$query = $query->getCompiler()->delete();

		$this->assertEquals('DELETE FROM "foobar" WHERE "id" = ?', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicUpdate()
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->update(['foo' => 'bar']);

		$this->assertEquals('UPDATE "foobar" SET "foo" = ?', $query['sql']);
		$this->assertEquals(['bar'], $query['params']);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage mako\database\query\compilers\Compiler::buildJsonSet(): The [ mako\database\query\compilers\Compiler ] query compiler does not support the unified JSON field syntax.
	 */
	public function testUpdateWithJSONColumn()
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->update(['data->foo->bar->0' => 1]);
	}

	/**
	 *
	 */
	public function testUpdateWithWhere()
	{
		$query = $this->getBuilder();

		$query->where('id', '=', 1);

		$query = $query->getCompiler()->update(['foo' => 'bar']);

		$this->assertEquals('UPDATE "foobar" SET "foo" = ? WHERE "id" = ?', $query['sql']);
		$this->assertEquals(['bar', 1], $query['params']);
	}

	/**
	 *
	 */
	public function testCountAggregate()
	{
		$query = $this->getBuilder();

		$query->count();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT COUNT(*) FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		$query = $this->getBuilder();

		$query->count('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT COUNT("foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testCountDistinctAggregate()
	{
		$query = $this->getBuilder();

		$query->countDistinct('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT COUNT(DISTINCT "foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testCountDistinctAggregateWithMultipleColumns()
	{
		$query = $this->getBuilder();

		$query->countDistinct(['foo', 'bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT COUNT(DISTINCT "foo", "bar") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testAvgAggregate()
	{
		$query = $this->getBuilder();

		$query->avg('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT AVG("foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testMaxAggregate()
	{
		$query = $this->getBuilder();

		$query->max('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT MAX("foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testMinAggregate()
	{
		$query = $this->getBuilder();

		$query->min('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT MIN("foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSumAggregate()
	{
		$query = $this->getBuilder();

		$query->sum('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT SUM("foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testAggregateWithRaw()
	{
		$query = $this->getBuilder();

		$query->count(new Raw('DISTINCT "foo"'));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT COUNT(DISTINCT "foo") FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testColumnWithoutParam()
	{
		$query = $this->getBuilder();

		$query->select(['id']);

		$query->column();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "id" FROM "foobar" LIMIT 1', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testColumnWithParam()
	{
		$query = $this->getBuilder();

		$query->column('id');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "id" FROM "foobar" LIMIT 1', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testUnion()
	{
		$query = $this->getBuilder('sales2016');

		$query->union(function($query)
		{
			$query->table('sales2015');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" UNION SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testMultipleUnions()
	{
		$query = $this->getBuilder('sales2016');

		$query->union(function($query)
		{
			$query->table('sales2014');
		});

		$query->union(function($query)
		{
			$query->table('sales2015');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2014" UNION SELECT * FROM "sales2015" UNION SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testUnionAll()
	{
		$query = $this->getBuilder('sales2016');

		$query->unionAll(function($query)
		{
			$query->table('sales2015');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" UNION ALL SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testIntersect()
	{
		$query = $this->getBuilder('sales2016');

		$query->intersect(function($query)
		{
			$query->table('sales2015');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" INTERSECT SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testIntersectAll()
	{
		$query = $this->getBuilder('sales2016');

		$query->intersectAll(function($query)
		{
			$query->table('sales2015');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" INTERSECT ALL SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testExcept()
	{
		$query = $this->getBuilder('sales2016');

		$query->except(function($query)
		{
			$query->table('sales2015');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" EXCEPT SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testExceptAll()
	{
		$query = $this->getBuilder('sales2016');

		$query->exceptAll(function($query)
		{
			$query->table('sales2015');
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "sales2015" EXCEPT ALL SELECT * FROM "sales2016"', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBatch()
	{
		$builder = Mockery::mock('\mako\database\query\Query[limit,offset,all]', [$this->getConnection()]);

		$builder->shouldReceive('limit')->once()->with(5);

		$builder->shouldReceive('offset')->once()->with(5);

		$builder->shouldReceive('offset')->once()->with(10);

		$builder->shouldReceive('offset')->once()->with(15);

		$builder->shouldReceive('offset')->once()->with(20);

		$builder->shouldReceive('all')->times(5)->andReturn([5], [5], [5], [5], []);

		$batches = 0;

		$builder->ascending('id')->batch(function($results) use (&$batches)
		{
			$this->assertEquals([5], $results);

			$batches++;
		}, 5);

		$this->assertEquals(4, $batches);

		//

		$builder = Mockery::mock('\mako\database\query\Query[limit,offset,all]', [$this->getConnection()]);

		$builder->shouldReceive('limit')->once()->with(5);

		$builder->shouldReceive('offset')->once()->with(5);

		$builder->shouldReceive('offset')->once()->with(10);

		$builder->shouldReceive('offset')->once()->with(15);

		$builder->shouldReceive('offset')->once()->with(20);

		$builder->shouldReceive('all')->times(4)->andReturn([5], [5], [5], []);

		$batches = 0;

		$builder->ascending('id')->batch(function($results) use (&$batches)
		{
			$this->assertEquals([5], $results);

			$batches++;
		}, 5, 5);

		$this->assertEquals(3, $batches);

		//

		$builder = Mockery::mock('\mako\database\query\Query[limit,offset,all]', [$this->getConnection()]);

		$builder->shouldReceive('limit')->once()->with(5);

		$builder->shouldReceive('offset')->once()->with(5);

		$builder->shouldReceive('offset')->once()->with(10);

		$builder->shouldReceive('all')->times(2)->andReturn([5], [5]);

		$batches = 0;

		$builder->ascending('id')->batch(function($results) use (&$batches)
		{
			$this->assertEquals([5], $results);

			$batches++;
		}, 5, 5, 15);

		$this->assertEquals(2, $batches);
	}
}
