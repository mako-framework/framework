<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\session\stores;

use PHPUnit_Framework_TestCase;

use mako\session\stores\Void;

/**
 * @group unit
 */
class VoidTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testWrite()
	{
		$null = new Void;

		$null->write('123', 'data', 123);
	}

	/**
	 *
	 */
	public function testRead()
	{
		$null = new Void;

		$this->assertEquals([], $null->read('123'));
	}

	/**
	 *
	 */
	public function testDelete()
	{
		$null = new Void;

		$null->delete('123');

	}

	/**
	 *
	 */
	public function testGc()
	{
		$null = new Void;

		$null->gc(123);
	}
}