<?php

namespace mako\tests\unit\security;

use \mako\security\Comparer;

/**
 * @group unit
 */

class ComparerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 */

	public function testCompare()
	{
		$this->assertTrue(Comparer::compare('foo', 'foo'));
		
		$this->assertFalse(Comparer::compare('foo', 'bar'));

		$this->assertFalse(Comparer::compare('foo', 'fooo'));
	}
}