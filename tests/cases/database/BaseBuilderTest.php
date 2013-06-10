<?php

use Mockery as m;
use \mako\Database;
use \mako\database\Query;
use \mako\database\query\Subquery;

class BaseBuilderTest extends PHPUnit_Framework_TestCase
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

		$connection->shouldReceive('getDriver')->andReturn('sqlite');

		return $connection;
	}

	/**
	 * 
	 */

	protected function getBuilder($table = 'foobar')
	{
		return new Query($this->getConnection(), $table);
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

	public function testSelectWithCloumns()
	{
		$query = $this->getBuilder();

		$query->columns(array('foo', 'bar'));

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

		$query->columns(array('foo', 'bar as baz'));

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

		$query->columns(array('foo', 'foobar.bar'));

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

	public function testSelectWithIn()
	{
		$query = $this->getBuilder();

		$query->in('foo', array(1, 2, 3));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "foo" IN (?, ?, ?)', $query['sql']);
		$this->assertEquals(array(1, 2, 3), $query['params']);
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

	public function testSelectWithExists()
	{
		$query = $this->getBuilder();

		$query->exists(new Subquery($this->getBuilder('barfoo')->where('barfoo.foobar_id', '=', Database::raw('foobar.id'))));

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

		$query->notExists(new Subquery($this->getBuilder('barfoo')->where('barfoo.foobar_id', '=', Database::raw('foobar.id'))));

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE NOT EXISTS (SELECT * FROM "barfoo" WHERE "barfoo"."foobar_id" = foobar.id)', $query['sql']);
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
}