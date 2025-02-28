<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\throttle\stores;

use DateTime;
use mako\redis\Redis;
use mako\tests\TestCase;
use mako\throttle\stores\Redis as RedisStore;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class RedisTest extends TestCase
{
	/**
	 *
	 */
	public function testGetHits(): void
	{
		/** @var Mockery\MockInterface&Redis $redis */
		$redis = Mockery::mock(Redis::class);

		$redis->shouldReceive('get')->once()->andReturn(42);

		$store = new RedisStore($redis);

		$this->assertSame(42, $store->getHits('foobar'));
	}

	/**
	 *
	 */
	public function testGetHitsWithMissingKey(): void
	{
		/** @var Mockery\MockInterface&Redis $redis */
		$redis = Mockery::mock(Redis::class);

		$redis->shouldReceive('get')->once()->andReturn(null);

		$store = new RedisStore($redis);

		$this->assertSame(0, $store->getHits('foobar'));
	}

	/**
	 *
	 */
	public function testGetExpiration(): void
	{
		/** @var Mockery\MockInterface&Redis $redis */
		$redis = Mockery::mock(Redis::class);

		$redis->shouldReceive('ttl')->once()->andReturn(3600);

		$store = new RedisStore($redis);

		$this->assertEqualsWithDelta(time() + 3600, $store->getExpiration('foobar')->getTimestamp(), 1);
	}

	/**
	 *
	 */
	public function testGetExpirationWithMissingKey(): void
	{
		/** @var Mockery\MockInterface&Redis $redis */
		$redis = Mockery::mock(Redis::class);

		$redis->shouldReceive('ttl')->once()->andReturn(-1);

		$store = new RedisStore($redis);

		$this->assertNull($store->getExpiration('foobar'));
	}

	/**
	 *
	 */
	public function testIncrement(): void
	{
		/** @var Mockery\MockInterface&Redis $redis */
		$redis = Mockery::mock(Redis::class);

		$redis->shouldReceive('pipeline')->once()->andReturn([true, 42]);

		$store = new RedisStore($redis);

		$this->assertSame(42, $store->increment('foobar', new DateTime));
	}
}
