<?php

use Mockery as m;
use \mako\database\Query;

class FirebirdBuilderTest extends PHPUnit_Framework_TestCase
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

		$connection->shouldReceive('getDriver')->andReturn('firebird');

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

		$this->assertEquals('SELECT * FROM "foobar" ROWS 1  TO 10', $query['sql']);
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

		$this->assertEquals('SELECT * FROM "foobar" ROWS 11 TO 20', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}
}