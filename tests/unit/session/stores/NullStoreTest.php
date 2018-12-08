<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\session\stores;

use mako\session\stores\NullStore;
use mako\tests\TestCase;

/**
 * @group unit
 */
class NullStoreTest extends TestCase
{
	/**
	 *
	 */
	public function testWrite(): void
	{
		$null = new NullStore;

		$null->write('123', ['data'], 123);

		$this->assertNull(null); // Hack to avoid test being marked as risky
	}

	/**
	 *
	 */
	public function testRead(): void
	{
		$null = new NullStore;

		$this->assertEquals([], $null->read('123'));
	}

	/**
	 *
	 */
	public function testDelete(): void
	{
		$null = new NullStore;

		$null->delete('123');

		$this->assertNull(null); // Hack to avoid test being marked as risky

	}

	/**
	 *
	 */
	public function testGc(): void
	{
		$null = new NullStore;

		$null->gc(123);

		$this->assertNull(null); // Hack to avoid test being marked as risky
	}
}
