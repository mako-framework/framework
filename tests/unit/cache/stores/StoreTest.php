<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\cache\stores;

use mako\cache\stores\Store;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

abstract class TestStore extends Store
{
	public function prefixedKey($key)
	{
		return $this->getPrefixedKey($key);
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('unit')]
class StoreTest extends TestCase
{
	/**
	 *
	 */
	protected function getStore(): MockInterface&TestStore
	{
		$mock =  Mockery::mock(TestStore::class . '[put,has,get,remove,clear]');

		return $mock->makePartial();
	}

	/**
	 *
	 */
	public function testPutIfNotExistsExisting(): void
	{
		$store = $this->getStore();

		$store->shouldReceive('has')->once()->with('foo')->andReturn(true);

		$this->assertFalse($store->putIfNotExists('foo', 'bar'));
	}

	/**
	 *
	 */
	public function testPutIfNotExistsNonExisting(): void
	{
		$store = $this->getStore();

		$store->shouldReceive('has')->once()->with('foo')->andReturn(false);

		$store->shouldReceive('put')->once()->with('foo', 'bar', 0);

		$this->assertFalse($store->putIfNotExists('foo', 'bar'));
	}

	/**
	 *
	 */
	public function testSetAndGetPrefix(): void
	{
		$store = $this->getStore();

		$store->setPrefix('foo');

		$this->assertSame('foo', $store->getPrefix());
	}

	/**
	 *
	 */
	public function testGetPrefixedKey(): void
	{
		$store = $this->getStore();

		$this->assertSame('bar', $store->prefixedKey('bar'));

		$store->setPrefix('foo');

		$this->assertSame('foo.bar', $store->prefixedKey('bar'));
	}

	/**
	 *
	 */
	public function testGetAndPutExisting(): void
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn('foobar');

		$store->shouldReceive('put')->once()->with('foo', 'barfoo', 3600)->andReturn(true);

		$this->assertEquals('foobar', $store->getAndPut('foo', 'barfoo', 3600));
	}

	/**
	 *
	 */
	public function testGetAndPutNonExisting(): void
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn(false);

		$store->shouldReceive('put')->once()->with('foo', 'barfoo', 3600)->andReturn(true);

		$this->assertEquals(false, $store->getAndPut('foo', 'barfoo', 3600));
	}

	/**
	 *
	 */
	public function testGetAndRemoveExisting(): void
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn('foobar');

		$store->shouldReceive('remove')->once()->with('foo')->andReturn(true);

		$this->assertEquals('foobar', $store->getAndRemove('foo'));
	}

	/**
	 *
	 */
	public function testGetAndRemoveNonExisting(): void
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn(null);

		$store->shouldReceive('remove')->never();

		$this->assertEquals(null, $store->getAndRemove('foo'));
	}

	/**
	 *
	 */
	public function testGetOrElseExisting(): void
	{
		$closure = fn () => 'from closure';

		$store = $this->getStore();

		$store->shouldReceive('get')->with('foo')->andReturn('from cache');

		$cached = $store->getOrElse('foo', $closure);

		$this->assertEquals('from cache', $cached);

	}

	/**
	 *
	 */
	public function testGetOrElseNonExisting(): void
	{
		$closure = fn () => 'from closure';

		$store = $this->getStore();

		$store->shouldReceive('get')->with('foo')->andReturn(null);

		$store->shouldReceive('put')->with('foo', 'from closure', 0);

		$cached = $store->getOrElse('foo', $closure);

		$this->assertEquals('from closure', $cached);
	}
}
