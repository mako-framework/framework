<?php

namespace mako\tests\unit\database\query;

use Mockery as m;

use mako\database\query\Query;

use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */
class QueryTest extends PHPUnit_Framework_TestCase
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
	public function getQuery()
	{
		$connection = m::mock('mako\database\connections\Connection');

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(m::mock('\mako\database\query\helpers\HelperInterface'));

		$connection->shouldReceive('getQueryCompiler')->andReturn(m::mock('\mako\database\query\compilers\Compiler'));

		return new Query($connection);
	}

	/**
	 *
	 */
	public function testFrom()
	{
		$query = $this->getQuery();

		$query->from('foobar');

		$this->assertSame('foobar', $query->getTable());
	}

	public function testInto()
	{
		$query = $this->getQuery();

		$query->from('foobar');

		$this->assertSame('foobar', $query->getTable());
	}
}