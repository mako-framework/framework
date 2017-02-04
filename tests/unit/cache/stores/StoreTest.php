<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\cache\stores;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\cache\stores\Store;

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

/**
 * @group unit
 */
class StoreTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 *
	 */
	protected function getStore()
	{
		return Mockery::mock(TestStore::class . '[put,has,get,remove,clear]')->makePartial();
	}

	/**
	 *
	 */
	public function testPutIfNotExistsExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('has')->once()->with('foo')->andReturn(true);

		$this->assertFalse($store->putIfNotExists('foo', 'bar'));
	}

	/**
	 *
	 */
	public function testPutIfNotExistsNonExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('has')->once()->with('foo')->andReturn(false);

		$store->shouldReceive('put')->once()->with('foo', 'bar', 0);

		$this->assertFalse($store->putIfNotExists('foo', 'bar'));
	}

	/**
	 *
	 */
	public function testSetAndGetPrefix()
	{
		$store = $this->getStore();

		$store->setPrefix('foo');

		$this->assertSame('foo', $store->getPrefix());
	}

	/**
	 *
	 */
	public function testGetPrefixedKey()
	{
		$store = $this->getStore();

		$this->assertSame('bar', $store->prefixedKey('bar'));

		$store->setPrefix('foo');

		$this->assertSame('foo.bar', $store->prefixedKey('bar'));
	}

	/**
	 *
	 */
	public function testGetAndPutExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn('foobar');

		$store->shouldReceive('put')->once()->with('foo', 'barfoo', 3600)->andReturn(true);

		$this->assertEquals('foobar', $store->getAndPut('foo', 'barfoo', 3600));
	}

	/**
	 *
	 */
	public function testGetAndPutNonExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn(false);

		$store->shouldReceive('put')->once()->with('foo', 'barfoo', 3600)->andReturn(true);

		$this->assertEquals(false, $store->getAndPut('foo', 'barfoo', 3600));
	}

	/**
	 *
	 */
	public function testGetAndRemoveExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn('foobar');

		$store->shouldReceive('remove')->once()->with('foo')->andReturn(true);

		$this->assertEquals('foobar', $store->getAndRemove('foo'));
	}

	/**
	 *
	 */
	public function testGetAndRemoveNonExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn(false);

		$store->shouldReceive('remove')->never();

		$this->assertEquals(false, $store->getAndRemove('foo'));
	}

	/**
	 *
	 */
	public function testGetOrElseExisting()
	{
		$closure = function(){ return 'from closure'; };

		$store = $this->getStore();

		$store->shouldReceive('has')->with('foo')->andReturn(true);

		$store->shouldReceive('get')->with('foo')->andReturn('from cache');

		$cached = $store->getOrElse('foo', $closure);

		$this->assertEquals('from cache', $cached);

	}

	/**
	 *
	 */
	public function testGetOrElseNonExisting()
	{
		$closure = function(){ return 'from closure'; };

		$store = $this->getStore();

		$store->shouldReceive('has')->with('foo')->andReturn(false);

		$store->shouldReceive('put')->with('foo', 'from closure', 0);

		$cached = $store->getOrElse('foo', $closure);

		$this->assertEquals('from closure', $cached);
	}
}
