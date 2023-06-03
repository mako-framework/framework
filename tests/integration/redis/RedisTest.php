<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\redis;

use mako\redis\Connection;
use mako\redis\exceptions\RedisException;
use mako\redis\Redis;
use mako\tests\TestCase;

// --------------------------------------------------------------------------
// We're only testing a small set of Redis commands since we're just testing
// if the client can build all types of commands and handle
// the different response types correctly.
// --------------------------------------------------------------------------

/**
 * @group integration
 * @group integration:redis
 */
class RedisTest extends TestCase
{
	/**
	 * @var \mako\redis\Redis
	 */
	protected $redis;

	/**
	 *
	 */
	public function setUp(): void
	{
		try
		{
			$this->redis = new Redis(new Connection('localhost', 6379));
		}
		catch(RedisException $e)
		{
			$this->markTestSkipped('Unable to connect to redis server.');
		}
	}

	/**
	 *
	 */
	public function tearDown(): void
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
	public function testGetConnectionAndGetName(): void
	{
		$connection = $this->redis->getConnection();

		$this->assertInstanceOf(Connection::class, $connection);
	}

	/**
	 *
	 */
	public function testPing(): void
	{
		$this->assertEquals('PONG', $this->redis->ping());
	}

	/**
	 *
	 */
	public function testSet(): void
	{
		$this->assertEquals('OK', $this->redis->set('myKey', 'hello'));
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$this->assertEquals('OK', $this->redis->set('myKey', 'hello'));

		$this->assertEquals('hello', $this->redis->get('myKey'));

		$this->assertNull($this->redis->get('doesNotExist'));
	}

	/**
	 *
	 */
	public function testIncr(): void
	{
		$this->assertEquals('OK', $this->redis->set('myKey', 10));

		$this->assertEquals(11, $this->redis->incr('myKey'));
	}

	/**
	 *
	 */
	public function testEval(): void
	{
		$this->assertEquals(['foo', 'bar', 'baz', 'bax'], $this->redis->eval('return {KEYS[1],KEYS[2],ARGV[1],ARGV[2]}', 2, 'foo', 'bar', 'baz', 'bax'));
	}

	/**
	 *
	 */
	public function testPipeline(): void
	{
		$this->assertEquals('OK', $this->redis->set('myKey', 0));

		$this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $this->redis->pipeline(function ($redis): void
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
	public function testMultipleWordCommands(): void
	{
		$this->assertEquals('OK', $this->redis->clientSetname('mako-redis'));

		$this->assertEquals('mako-redis', $this->redis->client_getname());
	}

	/**
	 *
	 */
	public function testUnknownCommand(): void
	{
		$this->expectException(RedisException::class);

		$this->redis->fooBarBaz();
	}

	/**
	 *
	 */
	public function testMissingParameter(): void
	{
		$this->expectException(RedisException::class);

		$this->redis->set('foo');
	}
}
