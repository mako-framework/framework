<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\session\stores;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\session\stores\Redis;

/**
 * @group unit
 */
class RedisTest extends PHPUnit_Framework_TestCase
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
	public function getRedisClient()
	{
		return Mockery::mock('mako\redis\Redis');
	}

	/**
	 *
	 */
	public function testWrite()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('setex')->once()->with('sess_123', 123, serialize(['data']));

		$redis = new Redis($client);

		$redis->write('123', ['data'], 123);
	}

	/**
	 *
	 */
	public function testRead()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('get')->once()->with('sess_123')->andReturn(serialize(['data']));

		$redis = new Redis($client);

		$cached = $redis->read('123');

		$this->assertEquals(['data'], $cached);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('get')->once()->with('sess_123')->andReturn(null);

		$redis = new Redis($client);

		$cached = $redis->read('123');

		$this->assertEquals([], $cached);
	}

	/**
	 *
	 */
	public function testDelete()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('del')->once()->with('sess_123');

		$redis = new Redis($client);

		$redis->delete('123');
	}

	/**
	 *
	 */
	public function testGc()
	{
		$client = $this->getRedisClient();

		$redis = new Redis($client);

		$redis->gc(123);
	}
}
