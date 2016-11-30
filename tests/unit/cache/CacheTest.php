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
		$closure = function(){};

		//

		$store = $this->getStore();

		$store->shouldReceive('getOrElse')->with('foo', $closure, 0)->andReturn('from cache');

		$cache = new Cache($store);

		$cached = $cache->getOrElse('foo', $closure);

		$this->assertEquals('from cache', $cached);

		//

		$store = $this->getStore();

		$store->shouldReceive('getOrElse')->with('foo', $closure, 3600)->andReturn('from cache');

		$cache = new Cache($store);

		$cached = $cache->getOrElse('foo', $closure, 3600);

		$this->assertEquals('from cache', $cached);

		//

		$store = $this->getStore();

		$store->shouldReceive('getOrElse')->with('baz.foo', $closure, 3600)->andReturn('from cache');

		$cache = new Cache($store, 'baz');

		$cached = $cache->getOrElse('foo', $closure, 3600);

		$this->assertEquals('from cache', $cached);
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

	/**
	 *
	 */
	public function testGetAndPutExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn('foobar');

		$store->shouldReceive('put')->once()->with('foo', 'barfoo', 3600)->andReturn(true);

		$cache = new Cache($store);

		$this->assertEquals('foobar', $cache->getAndPut('foo', 'barfoo', 3600));
	}

	/**
	 *
	 */
	public function testGetAndPutNonExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn(false);

		$store->shouldReceive('put')->once()->with('foo', 'barfoo', 3600)->andReturn(true);

		$cache = new Cache($store);

		$this->assertEquals(false, $cache->getAndPut('foo', 'barfoo', 3600));
	}

	/**
	 *
	 */
	public function testGetAndRemoveExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn('foobar');

		$store->shouldReceive('remove')->once()->with('foo')->andReturn(true);

		$cache = new Cache($store);

		$this->assertEquals('foobar', $cache->getAndRemove('foo'));
	}

	/**
	 *
	 */
	public function testGetAndRemoveNonExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('get')->once()->with('foo')->andReturn(false);

		$store->shouldReceive('remove')->never();

		$cache = new Cache($store);

		$this->assertEquals(false, $cache->getAndRemove('foo'));
	}
}
