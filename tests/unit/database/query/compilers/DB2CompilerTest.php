<?php

namespace mako\tests\unit\database\query\compilers;

use mako\database\query\Query;

use \Mockery as m;

/**
 * @group unit
 */

class DB2CompilerTest extends \PHPUnit_Framework_TestCase
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

		$connection->shouldReceive('getDialect')->andReturn('db2');

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

		$this->assertEquals('SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS mako_rownum FROM "foobar") AS mako1 WHERE mako_rownum BETWEEN 1 AND 10', $query['sql']);
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

		$this->assertEquals('SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS mako_rownum FROM "foobar") AS mako1 WHERE mako_rownum BETWEEN 11 AND 20', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}
}