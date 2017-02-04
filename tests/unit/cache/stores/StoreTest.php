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

		//

		$store = $this->getStore()->setPrefix('bar');

		$store->shouldReceive('get')->once()->with('bar.foo')->andReturn('foobar');

		$store->shouldReceive('put')->once()->with('bar.foo', 'barfoo', 3600)->andReturn(true);

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

		//

		$store = $this->getStore()->setPrefix('bar');

		$store->shouldReceive('get')->once()->with('bar.foo')->andReturn(false);

		$store->shouldReceive('put')->once()->with('bar.foo', 'barfoo', 3600)->andReturn(true);

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

		//

		$store = $this->getStore()->setPrefix('bar');

		$store->shouldReceive('get')->once()->with('bar.foo')->andReturn('foobar');

		$store->shouldReceive('remove')->once()->with('bar.foo')->andReturn(true);

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

		//

		$store = $this->getStore()->setPrefix('bar');

		$store->shouldReceive('get')->once()->with('bar.foo')->andReturn(false);

		$store->shouldReceive('remove')->never();

		$this->assertEquals(false, $store->getAndRemove('foo'));
	}

	/**
	 *
	 */
	public function testGetOrElseExisting()
	{
		$closure = function(){ return 'from closure'; };

		//

		$store = $this->getStore();

		$store->shouldReceive('has')->with('foo')->andReturn(true);

		$store->shouldReceive('get')->with('foo')->andReturn('from cache');

		$cached = $store->getOrElse('foo', $closure);

		$this->assertEquals('from cache', $cached);

		//

		$store = $this->getStore()->setPrefix('bar');

		$store->shouldReceive('has')->with('bar.foo')->andReturn(true);

		$store->shouldReceive('get')->with('bar.foo')->andReturn('from cache');

		$cached = $store->getOrElse('foo', $closure);

		$this->assertEquals('from cache', $cached);

	}

	/**
	 *
	 */
	public function testGetOrElseNonExisting()
	{
		$closure = function(){ return 'from closure'; };

		//

		$store = $this->getStore();

		$store->shouldReceive('has')->with('foo')->andReturn(false);

		$store->shouldReceive('put')->with('foo', 'from closure', 0);

		$cached = $store->getOrElse('foo', $closure);

		$this->assertEquals('from closure', $cached);

		//

		$store = $this->getStore()->setPrefix('bar');

		$store->shouldReceive('has')->with('bar.foo')->andReturn(false);

		$store->shouldReceive('put')->with('bar.foo', 'from closure', 0);

		$cached = $store->getOrElse('foo', $closure);

		$this->assertEquals('from closure', $cached);
	}
}
