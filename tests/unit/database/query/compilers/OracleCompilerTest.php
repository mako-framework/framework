<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use Mockery;

use mako\database\query\Query;
use mako\tests\TestCase;

/**
 * @group unit
 */
class OracleCompilerTest extends TestCase
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
			return new \mako\database\query\compilers\Oracle($query);
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
	public function testSelectWithNoLimit()
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar"', $query['sql']);

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

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY (SELECT 0) FETCH FIRST 10 ROWS ONLY', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimitAndOrder()
	{
		$query = $this->getBuilder();

		$query->orderBy('foo', 'DESC');

		$query->limit(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo" DESC FETCH FIRST 10 ROWS ONLY', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOffset()
	{
		$query = $this->getBuilder();

		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY (SELECT 0) OFFSET 10 ROWS', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOffsetAndOrder()
	{
		$query = $this->getBuilder();

		$query->orderBy('foo', 'DESC');

		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo" DESC OFFSET 10 ROWS', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY (SELECT 0) OFFSET 10 ROWS FETCH NEXT 10 ROWS ONLY', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimitOffsetAndOrder()
	{
		$query = $this->getBuilder();

		$query->orderBy('foo', 'DESC');

		$query->limit(10);
		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" ORDER BY "foo" DESC OFFSET 10 ROWS FETCH NEXT 10 ROWS ONLY', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithJSONColumn()
	{
		$query = $this->getBuilder();

		$query->select(['json->foo->0->bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_VALUE("json", \'$."foo"[0]."bar"\') FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->"bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_VALUE("json", \'$."foo"[0]."\\\"bar"\') FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->\'bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_VALUE("json", \'$."foo"[0]."\'\'bar"\') FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->bar as jsonvalue']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_VALUE("json", \'$."foo"[0]."bar"\') AS "jsonvalue" FROM "foobar"', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['foobar.json->foo->0->bar as jsonvalue']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_VALUE("foobar"."json", \'$."foo"[0]."bar"\') AS "jsonvalue" FROM "foobar"', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" FOR UPDATE', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" FOR UPDATE', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" CUSTOM LOCK', $query['sql']);
		$this->assertEquals([], $query['params']);
	}
}
