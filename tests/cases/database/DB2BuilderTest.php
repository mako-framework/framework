<?php

use Mockery as m;
use \mako\database\Query;

class DB2BuilderTest extends PHPUnit_Framework_TestCase
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

		$connection->shouldReceive('getDriver')->andReturn('db2');

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

	public function testSelectWithLimit()
	{
		$query = $this->getBuilder();

		$query->limit(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS mako_rownum FROM "foobar") AS m1 WHERE mako_rownum BETWEEN 1 AND 10', $query['sql']);
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

		$this->assertEquals('SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS mako_rownum FROM "foobar") AS m1 WHERE mako_rownum BETWEEN 11 AND 20', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}
}