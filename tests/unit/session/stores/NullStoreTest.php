<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\session\stores;

use PHPUnit_Framework_TestCase;

use mako\session\stores\NullStore;

/**
 * @group unit
 */
class NullStoreTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testWrite()
	{
		$null = new NullStore;

		$null->write('123', ['data'], 123);
	}

	/**
	 *
	 */
	public function testRead()
	{
		$null = new NullStore;

		$this->assertEquals([], $null->read('123'));
	}

	/**
	 *
	 */
	public function testDelete()
	{
		$null = new NullStore;

		$null->delete('123');

	}

	/**
	 *
	 */
	public function testGc()
	{
		$null = new NullStore;

		$null->gc(123);
	}
}
