<?php

namespace mako\tests\unit\database\query\compilers;

use mako\database\Database;
use mako\database\query\Query;
use mako\database\query\Raw;
use mako\database\query\Subquery;

use \Mockery as m;

/**
 * @group unit
 */

class BaseCompilerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	protected function getConnection()
	{
		$connection = m::mock('\mako\database\Connection');

		$connection->shouldReceive('getDialect')->andReturn('sqlite');

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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithCloumns()
	{
		$query = $this->getBuilder();

		$query->select(array('foo', 'bar'));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "foo", "bar" FROM "foobar"', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithCloumnAlias()
	{
		$query = $this->getBuilder();

		$query->select(array('foo', 'bar as baz'));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "foo", "bar" AS "baz" FROM "foobar"', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithTablePrefix()
	{
		$query = $this->getBuilder();

		$query->select(array('foo', 'foobar.bar'));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "foo", "foobar"."bar" FROM "foobar"', $query['sql']);
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithPagination()
	{
		$pagination = m::mock('\mako\pagination\Pagination');

		$pagination->shouldReceive('limit')->andReturn(10);

		$pagination->shouldReceive('offset')->andReturn(10);

		$query = $this->getBuilder();

		$query->paginate($pagination);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" LIMIT 10 OFFSET 10', $query['sql']);
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array('bar'), $query['params']);
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
		$this->assertEquals(array('bar', 'foo'), $query['params']);
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
		$this->assertEquals(array('bar', 'baz'), $query['params']);
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
		$this->assertEquals(array('bar'), $query['params']);
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
		$this->assertEquals(array('bar', 'foo'), $query['params']);
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
		$this->assertEquals(array(1, 10), $query['params']);
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
		$this->assertEquals(array(1, 10, 21, 30), $query['params']);
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
		$this->assertEquals(array(1, 10), $query['params']);
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
		$this->assertEquals(array(1, 10, 21, 30), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithInAndOrIn()
	{
		$query = $this->getBuilder();

		$query->in('foo', array(1, 2, 3));

		$query->orIn('foo', array(4, 5, 6));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IN (?, ?, ?) OR "foo" IN (?, ?, ?)', $query['sql']);
		$this->assertEquals(array(1, 2, 3, 4, 5, 6), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithNotIn()
	{
		$query = $this->getBuilder();

		$query->notIn('foo', array(1, 2, 3));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" NOT IN (?, ?, ?)', $query['sql']);
		$this->assertEquals(array(1, 2, 3), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithNotInAndOrNotIn()
	{
		$query = $this->getBuilder();

		$query->notIn('foo', array(1, 2, 3));

		$query->orNotIn('foo', array(4, 5, 6));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" NOT IN (?, ?, ?) OR "foo" NOT IN (?, ?, ?)', $query['sql']);
		$this->assertEquals(array(1, 2, 3, 4, 5, 6), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithIsNull()
	{
		$query = $this->getBuilder();

		$query->null('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IS NULL', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithIsNullAndOrNull()
	{
		$query = $this->getBuilder();

		$query->null('foo');

		$query->orNull('bar');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IS NULL OR "bar" IS NULL', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithIsNotNull()
	{
		$query = $this->getBuilder();

		$query->notNull('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IS NOT NULL', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithIsNotNullAndOrNotNull()
	{
		$query = $this->getBuilder();

		$query->notNull('foo');

		$query->orNotNull('bar');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IS NOT NULL OR "bar" IS NOT NULL', $query['sql']);
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithGroupBy()
	{
		$query = $this->getBuilder('orders');

		$query->select(array('customer', new Raw('SUM(price) as sum')));

		$query->groupBy('customer');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer"', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithGroupByArray()
	{
		$query = $this->getBuilder('orders');

		$query->select(array('customer', 'order_date', new Raw('SUM(price) as sum')));

		$query->groupBy(array('customer', 'order_date'));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", "order_date", SUM(price) as sum FROM "orders" GROUP BY "customer", "order_date"', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithHaving()
	{
		$query = $this->getBuilder('orders');

		$query->select(array('customer', new Raw('SUM(price) as sum')));

		$query->groupBy('customer');
		$query->having(new Raw('SUM(price)'), '<', 2000);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer" HAVING SUM(price) < ?', $query['sql']);
		$this->assertEquals(array(2000), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithHavingAndOrHaving()
	{
		$query = $this->getBuilder('orders');

		$query->select(array('customer', new Raw('SUM(price) as sum')));

		$query->groupBy('customer');
		$query->having(new Raw('SUM(price)'), '<', 2000);
		$query->orHaving(new Raw('SUM(price)'), '>', 2000);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT "customer", SUM(price) as sum FROM "orders" GROUP BY "customer" HAVING SUM(price) < ? OR SUM(price) > ?', $query['sql']);
		$this->assertEquals(array(2000, 2000), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testSelectWithOrderArray()
	{
		$query = $this->getBuilder();

		$query->orderBy(array('foo', 'bar'), 'DESC');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo", "bar" DESC', $query['sql']);
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testBasicDelete()
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->delete();

		$this->assertEquals('DELETE FROM "foobar"', $query['sql']);
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(1), $query['params']);
	}

	/**
	 *
	 */

	public function testBasicUpdate()
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->update(array('foo' => 'bar'));

		$this->assertEquals('UPDATE "foobar" SET "foo" = ?', $query['sql']);
		$this->assertEquals(array('bar'), $query['params']);
	}

	/**
	 *
	 */

	public function testUpdateWithWhere()
	{
		$query = $this->getBuilder();

		$query->where('id', '=', 1);

		$query = $query->getCompiler()->update(array('foo' => 'bar'));

		$this->assertEquals('UPDATE "foobar" SET "foo" = ? WHERE "id" = ?', $query['sql']);
		$this->assertEquals(array('bar', 1), $query['params']);
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
		$this->assertEquals(array(), $query['params']);

		$query = $this->getBuilder();

		$query->count('foo');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT COUNT("foo") FROM "foobar"', $query['sql']);
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
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
		$this->assertEquals(array(), $query['params']);
	}

	/**
	 *
	 */

	public function testBatch()
	{
		$builder = m::mock('\mako\database\query\Query[limit,offset,all]', [$this->getConnection()]);

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

		$builder = m::mock('\mako\database\query\Query[limit,offset,all]', [$this->getConnection()]);

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

		$builder = m::mock('\mako\database\query\Query[limit,offset,all]', [$this->getConnection()]);

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
