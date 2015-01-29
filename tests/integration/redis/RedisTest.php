<?php

namespace mako\tests\integration\redis;

use mako\redis\Connection;
use mako\redis\Redis;
use mako\redis\RedisException;

// --------------------------------------------------------------------------
// We're only testing a small set of Redis commands since we're just testing
// if the client can build all types of commands and handle
// the different response types correctly.
// --------------------------------------------------------------------------

/**
 * @group integration
 * @group integration:redis
 */

class RedisTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	protected $redis;

	/**
	 *
	 */

	public function setUp()
	{
		try
		{
			$this->redis = new Redis(new Connection('localhost'));
		}
		catch(RedisException $e)
		{
			$this->markTestSkipped("Unable to connect to redis server.");
		}
	}

	/**
	 *
	 */

	public function tearDown()
	{
		if($this->redis !== null)
		{
			$this->redis->flushdb();

			$this->redis = null;
		}
	}

	/**
	 *
	 */

	public function testPing()
	{
		$this->assertEquals('PONG', $this->redis->ping());
	}

	/**
	 *
	 */

	public function testSet()
	{
		$this->assertEquals('OK', $this->redis->set('myKey', 'hello'));
	}

	/**
	 *
	 */

	public function testGet()
	{
		$this->assertEquals('OK', $this->redis->set('myKey', 'hello'));

		$this->assertEquals('hello', $this->redis->get('myKey'));

		$this->assertNull($this->redis->get('doesNotExist'));
	}

	/**
	 *
	 */

	public function testIncr()
	{
		$this->assertEquals('OK', $this->redis->set('myKey', 10));

		$this->assertEquals(11, $this->redis->incr('myKey'));
	}

	/**
	 *
	 */

	public function testEval()
	{
		$this->assertEquals(['foo', 'bar', 'baz', 'bax'], $this->redis->eval('return {KEYS[1],KEYS[2],ARGV[1],ARGV[2]}', 2, 'foo', 'bar', 'baz', 'bax'));
	}

	/**
	 *
	 */

	public function testPipeline()
	{
		$this->assertEquals('OK', $this->redis->set('myKey', 0));

		$this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $this->redis->pipeline(function($redis)
		{
			for($i = 0; $i < 10; $i++)
			{
				$redis->incr('myKey');
			}
		}));

		$this->assertEquals(10, $this->redis->get('myKey'));
	}

	/**
	 *
	 */

	public function testMultipleWordCommands()
	{
		$this->assertEquals('OK', $this->redis->clientSetname('mako-redis'));

		$this->assertEquals('mako-redis', $this->redis->client_getname());
	}

	/**
	 * @expectedException \mako\redis\RedisException
	 */

	public function testUnknownCommand()
	{
		$this->redis->fooBarBaz();
	}

	/**
	 * @expectedException \mako\redis\RedisException
	 */

	public function testMissingParameter()
	{
		$this->redis->set('foo');
	}
}