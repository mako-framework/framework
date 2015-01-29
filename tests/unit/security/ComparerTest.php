<?php

namespace mako\tests\unit\security;

use mako\security\Comparer;

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

		$this->assertTrue(Comparer::compare('', ''));

		$this->assertTrue(Comparer::compare(123, 123));

		$this->assertTrue(Comparer::compare(123, '123'));

		$this->assertTrue(Comparer::compare(null, null));

		$this->assertTrue(Comparer::compare(null, ''));

		$this->assertFalse(Comparer::compare('foo', 'bar'));

		$this->assertFalse(Comparer::compare('foo', ''));

		$this->assertFalse(Comparer::compare('', 'foo'));

		$this->assertFalse(Comparer::compare('foo', 'fooo'));

		$this->assertFalse(Comparer::compare('fooo', 'foo'));
	}
}