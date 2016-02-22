<?php

namespace mako\tests\unit\database\query\compilers;

use mako\database\query\Query;

use \Mockery as m;

/**
 * @group unit
 */
class NuoDBCompilerTest extends \PHPUnit_Framework_TestCase
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
		$connection = m::mock('\mako\database\connections\Connection');

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(m::mock('\mako\database\query\helpers\HelperInterface'));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function($query)
		{
			return new \mako\database\query\compilers\NuoDB($query);
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
	public function testSelectWithLimit()
	{
		$query = $this->getBuilder();

		$query->limit(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` LIMIT 10', $query['sql']);
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

		$this->assertEquals('SELECT * FROM `foobar` LIMIT 10 OFFSET 10', $query['sql']);
		$this->assertEquals(array(), $query['params']);
	}
}