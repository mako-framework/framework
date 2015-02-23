<?php

namespace mako\tests\unit\database\query\compilers;

use mako\database\query\Query;

use \Mockery as m;

/**
 * @group unit
 */

class SQLServerCompilerTest extends \PHPUnit_Framework_TestCase
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

		$connection->shouldReceive('getDialect')->andReturn('sqlsrv');

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

		$this->assertEquals('SELECT * FROM [foobar]', $query['sql']);
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

		$this->assertEquals('SELECT TOP 10 * FROM [foobar]', $query['sql']);
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

		$this->assertEquals('SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS mako_rownum FROM [foobar]) AS mako1 WHERE mako_rownum BETWEEN 11 AND 20', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}
}