<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query;

use mako\database\query\Result;
use mako\tests\TestCase;

/**
 * @group unit
 */
class ResultTest extends TestCase
{
	/**
	 *
	 */
	public function testToArray()
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$this->assertEquals(['foo' => 1, 'bar' => 2], $result->toArray());
	}

	/**
	 *
	 */
	public function testJsonSerialize()
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$this->assertEquals('{"foo":1,"bar":2}', json_encode($result));
	}

	/**
	 *
	 */
	public function testToJSON()
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$this->assertEquals('{"foo":1,"bar":2}', $result->toJSON());
	}

	/**
	 *
	 */
	public function testToString()
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$this->assertEquals('{"foo":1,"bar":2}', (string) $result);
	}
}
