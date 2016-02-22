<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\cache;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\cache\Cache;

/**
 * @group unit
 */
class CacheTest extends PHPUnit_Framework_TestCase
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
	public function getStore()
	{
		return Mockery::mock('\mako\cache\stores\StoreInterface');
	}

	/**
	 *
	 */
	public function testPut()
	{
		$store = $this->getStore();

		$store->shouldReceive('put')->once()->with('foo', 'bar', 0)->andReturn(true);

		$cache = new Cache($store);

		$cache->put('foo', 'bar');

		//

		$store = $this->getStore();

		$store->shouldReceive('put')->once()->with('foo', 'bar', 3600)->andReturn(true);

		$cache = new Cache($store);

		$cache->put('foo', 'bar', 3600);

		//

		$store = $this->getStore();

		$store->shouldReceive('put')->once()->with('baz.foo', 'bar', 3600)->andReturn(true);

		$cache = new Cache($store, 'baz');

		$cache->put('foo', 'bar', 3600);
	}

	/**
	 *
	 */
	public function testHas()
	{
		$store = $this->getStore();

		$store->shouldReceive('has')->once()->with('foo')->andReturn(true);

		$cache = new Cache($store);

		$cache->has('foo');

		//

		$store = $this->getStore();

		$store->shouldReceive('has')->once()->with('baz.foo')->andReturn(true);

		$cache = new Cache($store, 'baz');

		$cache->has('foo');
	}

	/**
	 *
	 */
	public function testGet()
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn(true);

		$cache = new Cache($store);

		$cache->get('foo');

		//

		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('baz.foo')->andReturn(true);

		$cache = new Cache($store, 'baz');

		$cache->get('foo');
	}

	/**
	 *
	 */
	public function testGetOrElse()
	{
		$store = $this->getStore();

		$store->shouldReceive('has')->once()->with('foo')->andReturn(true);

		$store->shouldReceive('get')->once()->with('foo')->andReturn('from cache');

		$cache = new Cache($store);

		$cached = $cache->getOrElse('foo', function(){});

		$this->assertEquals('from cache', $cached);

		//

		$store = $this->getStore();

		$store->shouldReceive('has')->once()->with('foo')->andReturn(false);

		$store->shouldReceive('put')->once()->with('foo', 'from closure', 0)->andReturn(true);

		$cache = new Cache($store);

		$cached = $cache->getOrElse('foo', function(){ return 'from closure'; });

		$this->assertEquals('from closure', $cached);

		//

		$store = $this->getStore();

		$store->shouldReceive('has')->once()->with('foo')->andReturn(false);

		$store->shouldReceive('put')->once()->with('foo', 'from closure', 3600)->andReturn(true);

		$cache = new Cache($store);

		$cached = $cache->getOrElse('foo', function(){ return 'from closure'; }, 3600);

		$this->assertEquals('from closure', $cached);

		//

		$store = $this->getStore();

		$store->shouldReceive('has')->once()->with('baz.foo')->andReturn(true);

		$store->shouldReceive('get')->once()->with('baz.foo')->andReturn('from cache');

		$cache = new Cache($store, 'baz');

		$cached = $cache->getOrElse('foo', function(){});

		$this->assertEquals('from cache', $cached);

		//

		$store = $this->getStore();

		$store->shouldReceive('has')->once()->with('baz.foo')->andReturn(false);

		$store->shouldReceive('put')->once()->with('baz.foo', 'from closure', 0)->andReturn(true);

		$cache = new Cache($store, 'baz');

		$cached = $cache->getOrElse('foo', function(){ return 'from closure'; });

		$this->assertEquals('from closure', $cached);

		//

		$store = $this->getStore();

		$store->shouldReceive('has')->once()->with('baz.foo')->andReturn(false);

		$store->shouldReceive('put')->once()->with('baz.foo', 'from closure', 3600)->andReturn(true);

		$cache = new Cache($store, 'baz');

		$cached = $cache->getOrElse('foo', function(){ return 'from closure'; }, 3600);

		$this->assertEquals('from closure', $cached);
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$store = $this->getStore();

		$store->shouldReceive('remove')->once()->with('foo')->andReturn(true);

		$cache = new Cache($store);

		$cache->remove('foo');

		//

		$store = $this->getStore();

		$store->shouldReceive('remove')->once()->with('baz.foo')->andReturn(true);

		$cache = new Cache($store, 'baz');

		$cache->remove('foo');
	}

	/**
	 *
	 */
	public function testClear()
	{
		$store = $this->getStore();

		$store->shouldReceive('clear')->once()->andReturn(true);

		$cache = new Cache($store);

		$cache->clear();
	}
}