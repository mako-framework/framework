<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query;

use mako\database\query\Result;
use mako\database\query\ResultSet;
use mako\tests\TestCase;

/**
 * @group unit
 */
class ResultSetTest extends TestCase
{
	/**
	 *
	 */
	public function testToArray()
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
	public function testJsonSerialize()
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
	public function testToJSON()
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$resultSet = new ResultSet([$result]);

		$this->assertEquals('[{"foo":1,"bar":2}]', $resultSet->toJSON());
	}

	/**
	 *
	 */
	public function testToString()
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$resultSet = new ResultSet([$result]);

		$this->assertEquals('[{"foo":1,"bar":2}]', (string) $resultSet);
	}
}
