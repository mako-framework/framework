<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query;

use PHPUnit_Framework_TestCase;

use mako\database\query\Result;

/**
 * @group unit
 */
class ResultTest extends PHPUnit_Framework_TestCase
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
