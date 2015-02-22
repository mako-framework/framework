<?php

namespace mako\tests\unit\session\stores;

use mako\session\stores\Null;

use \Mockery as m;

/**
 * @group unit
 */

class NullTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testWrite()
	{
		$null = new Null;

		$null->write('123', 'data', 123);
	}

	/**
	 *
	 */

	public function testRead()
	{
		$null = new Null;

		$this->assertEquals([], $null->read('123'));
	}

	/**
	 *
	 */

	public function testDelete()
	{
		$null = new Null;

		$null->delete('123');

	}

	/**
	 *
	 */

	public function testGc()
	{
		$null = new Null;

		$null->gc(123);
	}
}