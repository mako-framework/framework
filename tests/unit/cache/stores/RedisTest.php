<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cache\stores;

use mako\cache\stores\Redis;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class RedisTest extends TestCase
{
	/**
	 *
	 */
	public function getRedisClient()
	{
		return Mockery::mock('mako\redis\Redis');
	}

	/**
	 *
	 */
	public function testPut()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('foo', 123)->andReturn(true);

		$redis = new Redis($client);

		$redis->put('foo', 123);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('foo', serialize('foo'))->andReturn(true);

		$redis = new Redis($client);

		$redis->put('foo', 'foo');

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('setex')->once()->with('foo', 3600, 123)->andReturn(true);

		$redis = new Redis($client);

		$redis->put('foo', 123, 3600);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('setex')->once()->with('foo', 3600, serialize('foo'))->andReturn(true);

		$redis = new Redis($client);

		$redis->put('foo', 'foo', 3600);
	}

	/**
	 *
	 */
	public function testPutIfNotExists()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('setnx')->once()->with('foo', 123)->andReturn(true);

		$redis = new Redis($client);

		$redis->putIfNotExists('foo', 123);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('setnx')->once()->with('foo', serialize('foo'))->andReturn(true);

		$redis = new Redis($client);

		$redis->putIfNotExists('foo', 'foo');

		//

		$client = $this->getRedisClient();

		$lua = "return redis.call('exists', KEYS[1]) == 0 and redis.call('setex', KEYS[1], ARGV[1], ARGV[2])";

		$client->shouldReceive('eval')->once()->with($lua, 1, 'foo', 3600, 123)->andReturn(true);

		$redis = new Redis($client);

		$redis->putIfNotExists('foo', 123, 3600);

		//

		$client = $this->getRedisClient();

		$lua = "return redis.call('exists', KEYS[1]) == 0 and redis.call('setex', KEYS[1], ARGV[1], ARGV[2])";

		$client->shouldReceive('eval')->once()->with($lua, 1, 'foo', 3600, serialize('foo'))->andReturn(true);

		$redis = new Redis($client);

		$redis->putIfNotExists('foo', 'foo', 3600);
	}

	/**
	 *
	 */
	public function testIncrement()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('incrby')->once()->with('foo', 1)->andReturn(1);

		$redis = new Redis($client);

		$this->assertSame(1, $redis->increment('foo'));

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('incrby')->once()->with('foo', 10)->andReturn(10);

		$redis = new Redis($client);

		$this->assertSame(10, $redis->increment('foo', 10));
	}

	/**
	 *
	 */
	public function testDecrement()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('decrby')->once()->with('foo', 1)->andReturn(-1);

		$redis = new Redis($client);

		$this->assertSame(-1, $redis->decrement('foo'));

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('decrby')->once()->with('foo', 10)->andReturn(-10);

		$redis = new Redis($client);

		$this->assertSame(-10, $redis->decrement('foo', 10));
	}

	/**
	 *
	 */
	public function testHas()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('exists')->once()->with('foo')->andReturn(1);

		$redis = new Redis($client);

		$has = $redis->has('foo');

		$this->assertTrue($has);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('exists')->once()->with('foo')->andReturn(0);

		$redis = new Redis($client);

		$has = $redis->has('foo');

		$this->assertFalse($has);
	}

	/**
	 *
	 */
	public function testGet()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('get')->once()->with('foo')->andReturn(123);

		$redis = new Redis($client);

		$cached = $redis->get('foo');

		$this->assertEquals(123, $cached);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('get')->once()->with('foo')->andReturn(serialize('foo'));

		$redis = new Redis($client);

		$cached = $redis->get('foo');

		$this->assertEquals('foo', $cached);
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('del')->once()->with('foo')->andReturn(1);

		$redis = new Redis($client);

		$removed = $redis->remove('foo');

		$this->assertTrue($removed);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('del')->once()->with('foo')->andReturn(0);

		$redis = new Redis($client);

		$removed = $redis->remove('foo');

		$this->assertFalse($removed);
	}

	/**
	 *
	 */
	public function testClear()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('flushdb')->once();

		$redis = new Redis($client);

		$redis->clear();
	}
}
