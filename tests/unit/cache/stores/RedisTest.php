<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cache\stores;

use mako\cache\stores\Redis;
use mako\redis\Redis as RedisClient;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class RedisTest extends TestCase
{
	/**
	 *
	 */
	public function getRedisClient(): MockInterface&RedisClient
	{
		return Mockery::mock(RedisClient::class);
	}

	/**
	 *
	 */
	public function testPut(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('mako:cache:foo', 123)->andReturn(true);

		$redis = new Redis($client);

		$redis->put('foo', 123);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('mako:cache:foo', serialize('foo'))->andReturn(true);

		$redis = new Redis($client);

		$redis->put('foo', 'foo');

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('mako:cache:foo', 123, 'EX', 3600)->andReturn(true);

		$redis = new Redis($client);

		$redis->put('foo', 123, 3600);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('mako:cache:foo', serialize('foo'), 'EX', 3600)->andReturn(true);

		$redis = new Redis($client);

		$redis->put('foo', 'foo', 3600);
	}

	/**
	 *
	 */
	public function testPutIfNotExists(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('mako:cache:foo', 123, 'NX')->andReturn(true);

		$redis = new Redis($client);

		$redis->putIfNotExists('foo', 123);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('mako:cache:foo', serialize('foo'), 'NX')->andReturn(true);

		$redis = new Redis($client);

		$redis->putIfNotExists('foo', 'foo');

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('mako:cache:foo', 123, 'NX', 'EX', 3600)->andReturn(true);

		$redis = new Redis($client);

		$redis->putIfNotExists('foo', 123, 3600);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('mako:cache:foo', serialize('foo'), 'NX', 'EX', 3600)->andReturn(true);

		$redis = new Redis($client);

		$redis->putIfNotExists('foo', 'foo', 3600);
	}

	/**
	 *
	 */
	public function testIncrement(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('incrby')->once()->with('mako:cache:foo', 1)->andReturn(1);

		$redis = new Redis($client);

		$this->assertSame(1, $redis->increment('foo'));

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('incrby')->once()->with('mako:cache:foo', 10)->andReturn(10);

		$redis = new Redis($client);

		$this->assertSame(10, $redis->increment('foo', 10));
	}

	/**
	 *
	 */
	public function testDecrement(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('decrby')->once()->with('mako:cache:foo', 1)->andReturn(-1);

		$redis = new Redis($client);

		$this->assertSame(-1, $redis->decrement('foo'));

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('decrby')->once()->with('mako:cache:foo', 10)->andReturn(-10);

		$redis = new Redis($client);

		$this->assertSame(-10, $redis->decrement('foo', 10));
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('exists')->once()->with('mako:cache:foo')->andReturn(1);

		$redis = new Redis($client);

		$has = $redis->has('foo');

		$this->assertTrue($has);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('exists')->once()->with('mako:cache:foo')->andReturn(0);

		$redis = new Redis($client);

		$has = $redis->has('foo');

		$this->assertFalse($has);
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('get')->once()->with('mako:cache:foo')->andReturn(123);

		$redis = new Redis($client);

		$cached = $redis->get('foo');

		$this->assertEquals(123, $cached);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('get')->once()->with('mako:cache:foo')->andReturn(serialize('foo'));

		$redis = new Redis($client);

		$cached = $redis->get('foo');

		$this->assertEquals('foo', $cached);
	}

	/**
	 *
	 */
	public function testRemove(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('del')->once()->with('mako:cache:foo')->andReturn(1);

		$redis = new Redis($client);

		$removed = $redis->remove('foo');

		$this->assertTrue($removed);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('del')->once()->with('mako:cache:foo')->andReturn(0);

		$redis = new Redis($client);

		$removed = $redis->remove('foo');

		$this->assertFalse($removed);
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('keys')->once()->with('mako:cache:*')->andReturn(['mako:cache:foo', 'mako:cache:bar']);

		$client->shouldReceive('del')->once()->with('mako:cache:foo', 'mako:cache:bar')->andReturn(2);

		$redis = new Redis($client);

		$redis->clear();
	}
}
