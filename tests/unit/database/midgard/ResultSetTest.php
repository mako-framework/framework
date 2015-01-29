<?php

namespace mako\tests\unit\database\midgard;

use mako\database\midgard\ResultSet;

use \Mockery as m;

/**
 * @group unit
 */

class ResultSetTest extends \PHPUnit_Framework_TestCase
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

	public function testPluck()
	{
		$resultSet = new ResultSet([['foo' => 'bar'], ['foo' => 'bar']]);

		$plucked = $resultSet->pluck('foo');

		$this->assertEquals(['bar', 'bar'], $plucked);

		//

		$resultSet = new ResultSet([(object) ['foo' => 'bar'], (object) ['foo' => 'bar']]);

		$plucked = $resultSet->pluck('foo');

		$this->assertEquals(['bar', 'bar'], $plucked);
	}

	/**
	 *
	 */

	public function testToArray()
	{
		$mock1 = m::mock('\mako\database\midgard\ORM')->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar'])->getMock();

		$mock2 = m::mock('\mako\database\midgard\ORM')->shouldReceive('toArray')->once()->andReturn(['foo' => 'baz'])->getMock();

		$resultSet = new ResultSet([$mock1, $mock2]);

		$this->assertEquals([['foo' => 'bar'], ['foo' => 'baz']], $resultSet->toArray());
	}

	/**
	 *
	 */

	public function testToJson()
	{
		$mock1 = m::mock('\mako\database\midgard\ORM')->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar'])->getMock();

		$mock2 = m::mock('\mako\database\midgard\ORM')->shouldReceive('toArray')->once()->andReturn(['foo' => 'baz'])->getMock();

		$resultSet = new ResultSet([$mock1, $mock2]);

		$this->assertEquals('[{"foo":"bar"},{"foo":"baz"}]', $resultSet->toJson());
	}

	/**
	 *
	 */

	public function testToString()
	{
		$mock1 = m::mock('\mako\database\midgard\ORM')->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar'])->getMock();

		$mock2 = m::mock('\mako\database\midgard\ORM')->shouldReceive('toArray')->once()->andReturn(['foo' => 'baz'])->getMock();

		$resultSet = new ResultSet([$mock1, $mock2]);

		$this->assertEquals('[{"foo":"bar"},{"foo":"baz"}]', (string) $resultSet);
	}
}