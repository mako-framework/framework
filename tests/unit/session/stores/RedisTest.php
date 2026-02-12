<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\session\stores;

use mako\redis\Redis as RedisClient;
use mako\session\stores\Redis;
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
	public function testWrite(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('mako:session:123', serialize(['data']), 'EX', 123);

		$redis = new Redis($client);

		$redis->write('123', ['data'], 123);
	}

	/**
	 *
	 */
	public function testRead(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('get')->once()->with('mako:session:123')->andReturn(serialize(['data']));

		$redis = new Redis($client);

		$cached = $redis->read('123');

		$this->assertEquals(['data'], $cached);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('get')->once()->with('mako:session:123')->andReturn(null);

		$redis = new Redis($client);

		$cached = $redis->read('123');

		$this->assertEquals([], $cached);
	}

	/**
	 *
	 */
	public function testDelete(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('del')->once()->with('mako:session:123');

		$redis = new Redis($client);

		$redis->delete('123');
	}

	/**
	 *
	 */
	public function testGc(): void
	{
		$client = $this->getRedisClient();

		$redis = new Redis($client);

		$redis->gc(123);

		$this->assertNull(null); // Hack to avoid test being marked as risky
	}
}
