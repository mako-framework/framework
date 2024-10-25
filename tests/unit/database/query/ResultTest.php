<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query;

use mako\database\query\Result;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ResultTest extends TestCase
{
	/**
	 *
	 */
	public function testToArray(): void
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$this->assertEquals(['foo' => 1, 'bar' => 2], $result->toArray());
	}

	/**
	 *
	 */
	public function testJsonSerialize(): void
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$this->assertEquals('{"foo":1,"bar":2}', json_encode($result));
	}

	/**
	 *
	 */
	public function testToJSON(): void
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$this->assertEquals('{"foo":1,"bar":2}', $result->toJson());
	}

	/**
	 *
	 */
	public function testToString(): void
	{
		$result = new Result;

		$result->foo = 1;
		$result->bar = 2;

		$this->assertEquals('{"foo":1,"bar":2}', (string) $result);
	}
}
