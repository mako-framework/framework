<?php

namespace mako\tests\unit\session\stores;

use mako\session\stores\Redis;

use \Mockery as m;

/**
 * @group unit
 */

class RedisTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function getRedisClient()
	{
		return m::mock('mako\redis\Redis');
	}

	/**
	 *
	 */

	public function testWrite()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('setex')->once()->with('sess_123', 123, serialize('data'));

		$redis = new Redis($client);

		$redis->write('123', 'data', 123);
	}

	/**
	 *
	 */

	public function testRead()
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('get')->once()->with('sess_123')->andReturn(serialize('data'));

		$redis = new Redis($client);

		$cached = $redis->read('123');

		$this->assertEquals('data', $cached);

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