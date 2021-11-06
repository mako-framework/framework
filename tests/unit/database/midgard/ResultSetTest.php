<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\midgard;

use mako\database\midgard\ORM;
use mako\database\midgard\ResultSet;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class ResultSetTest extends TestCase
{
	/**
	 *
	 */
	public function testPluck(): void
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
	public function testProtect(): void
	{
		$mock1 = Mockery::mock(ORM::class)->shouldReceive('protect')->once()->with('foo')->getMock();

		$mock2 = Mockery::mock(ORM::class)->shouldReceive('protect')->once()->with('foo')->getMock();

		$resultSet = new ResultSet([$mock1, $mock2]);

		$resultSet->protect('foo');
	}

	/**
	 *
	 */
	public function testExpose(): void
	{
		$mock1 = Mockery::mock(ORM::class)->shouldReceive('expose')->once()->with('foo')->getMock();

		$mock2 = Mockery::mock(ORM::class)->shouldReceive('expose')->once()->with('foo')->getMock();

		$resultSet = new ResultSet([$mock1, $mock2]);

		$resultSet->expose('foo');
	}

	/**
	 *
	 */
	public function testToArray(): void
	{
		$mock1 = Mockery::mock(ORM::class)->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar'])->getMock();

		$mock2 = Mockery::mock(ORM::class)->shouldReceive('toArray')->once()->andReturn(['foo' => 'baz'])->getMock();

		$resultSet = new ResultSet([$mock1, $mock2]);

		$this->assertEquals([['foo' => 'bar'], ['foo' => 'baz']], $resultSet->toArray());
	}

	/**
	 *
	 */
	public function testJsonSerialize(): void
	{
		$mock1 = Mockery::mock(ORM::class)->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar'])->getMock();

		$mock2 = Mockery::mock(ORM::class)->shouldReceive('toArray')->once()->andReturn(['foo' => 'baz'])->getMock();

		$resultSet = new ResultSet([$mock1, $mock2]);

		$this->assertEquals('[{"foo":"bar"},{"foo":"baz"}]', json_encode($resultSet));
	}

	/**
	 *
	 */
	public function testToJson(): void
	{
		$mock1 = Mockery::mock(ORM::class)->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar'])->getMock();

		$mock2 = Mockery::mock(ORM::class)->shouldReceive('toArray')->once()->andReturn(['foo' => 'baz'])->getMock();

		$resultSet = new ResultSet([$mock1, $mock2]);

		$this->assertEquals('[{"foo":"bar"},{"foo":"baz"}]', $resultSet->toJson());
	}

	/**
	 *
	 */
	public function testToString(): void
	{
		$mock1 = Mockery::mock(ORM::class)->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar'])->getMock();

		$mock2 = Mockery::mock(ORM::class)->shouldReceive('toArray')->once()->andReturn(['foo' => 'baz'])->getMock();

		$resultSet = new ResultSet([$mock1, $mock2]);

		$this->assertEquals('[{"foo":"bar"},{"foo":"baz"}]', (string) $resultSet);
	}
}
