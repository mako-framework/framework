<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query;

use mako\database\query\Result;
use mako\database\query\ResultSet;
use mako\pagination\PaginationInterface;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ResultSetTest extends TestCase
{
	/**
	 *
	 */
	public function testToArray(): void
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$resultSet = new ResultSet([$result]);

		$this->assertEquals([['foo' => 1, 'bar' => 2]], $resultSet->toArray());
	}

	/**
	 *
	 */
	public function testJsonSerialize(): void
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$resultSet = new ResultSet([$result]);

		$this->assertEquals('[{"foo":1,"bar":2}]', json_encode($resultSet));
	}

	/**
	 *
	 */
	public function testJsonSerializeWithPagination(): void
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$resultSet = new ResultSet([$result]);

		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar']);

		$resultSet->setPagination($pagination);

		$this->assertEquals('{"data":[{"foo":1,"bar":2}],"pagination":{"foo":"bar"}}', json_encode($resultSet));
	}

	/**
	 *
	 */
	public function testToJSON(): void
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$resultSet = new ResultSet([$result]);

		$this->assertEquals('[{"foo":1,"bar":2}]', $resultSet->toJson());
	}

	/**
	 *
	 */
	public function testToString(): void
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$resultSet = new ResultSet([$result]);

		$this->assertEquals('[{"foo":1,"bar":2}]', (string) $resultSet);
	}
}
