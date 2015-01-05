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
		// Nothing to test since it doesn't write anything
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
		// Nothing to test since it doesn't delete anything

	}

	/**
	 * 
	 */

	public function testGc()
	{
		// Nothing to test since there isn't anything to clean up
	}
}