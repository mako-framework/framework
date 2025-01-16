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
use Mockery\Generator\MockConfigurationBuilder;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class RedisTest extends TestCase
{
	/**
	 * @return Mockery\MockInterface|RedisClient
	 */
	public function getRedisClient($ugly = false)
	{
		if (!$ugly) {
			return Mockery::mock(RedisClient::class);
		}

		// This is an extremely ugly workaround to mock the Redis client class.
		// This is needed since Mockery can't mock classes that have a method named eval.

		$mockConfigBuilder = new MockConfigurationBuilder;

		$mockConfigBuilder->setBlackListedMethods([
			'__call',
			'__callStatic',
			'__clone',
			'__wakeup',
			'__set',
			'__get',
			'__toString',
			'__isset',
			'__destruct',
			'__debugInfo',
			'__halt_compiler', 'abstract', 'and', 'array', 'as',
			'break', 'callable', 'case', 'catch', 'class',
			'clone', 'const', 'continue', 'declare', 'default',
			'die', 'do', 'echo', 'else', 'elseif',
			'empty', 'enddeclare', 'endfor', 'endforeach', 'endif',
			'endswitch', 'endwhile', 'exit', 'extends',
			'final', 'for', 'foreach', 'function', 'global',
			'goto', 'if', 'implements', 'include', 'include_once',
			'instanceof', 'insteadof', 'interface', 'isset', 'list',
			'namespace', 'new', 'or', 'print', 'private',
			'protected', 'public', 'require', 'require_once', 'return',
			'static', 'switch', 'throw', 'trait', 'try',
			'unset', 'use', 'var', 'while', 'xor',
			'callable', 'class', 'trait', 'extends', 'implements', 'static', 'abstract', 'final',
			'public', 'protected', 'private', 'const', 'enddeclare', 'endfor', 'endforeach', 'endif',
			'endwhile', 'and', 'global', 'goto', 'instanceof', 'insteadof', 'interface', 'namespace', 'new',
			'or', 'xor', 'try', 'use', 'var', 'exit', 'list', 'clone', 'include', 'include_once', 'throw',
			'array', 'print', 'echo', 'require', 'require_once', 'return', 'else', 'elseif', 'default',
			'break', 'continue', 'switch', 'yield', 'function', 'if', 'endswitch', 'finally', 'for', 'foreach',
			'declare', 'case', 'do', 'while', 'as', 'catch', 'die', 'self', 'parent',
		]);

		return Mockery::mock(RedisClient::class, $mockConfigBuilder);
	}

	/**
	 *
	 */
	public function testPut(): void
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

		$client->shouldReceive('set')->once()->with('foo', 123, 'EX', 3600)->andReturn(true);

		$redis = new Redis($client);

		$redis->put('foo', 123, 3600);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('foo', serialize('foo'), 'EX', 3600)->andReturn(true);

		$redis = new Redis($client);

		$redis->put('foo', 'foo', 3600);
	}

	/**
	 *
	 */
	public function testPutIfNotExists(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('foo', 123, 'NX')->andReturn(true);

		$redis = new Redis($client);

		$redis->putIfNotExists('foo', 123);

		//

		$client = $this->getRedisClient();

		$client->shouldReceive('set')->once()->with('foo', serialize('foo'), 'NX')->andReturn(true);

		$redis = new Redis($client);

		$redis->putIfNotExists('foo', 'foo');

		//

		$client = $this->getRedisClient(true);

		$lua = "return redis.call('exists', KEYS[1]) == 0 and redis.call('setex', KEYS[1], ARGV[1], ARGV[2])";

		$client->shouldReceive('eval')->once()->with($lua, 1, 'foo', 3600, 123)->andReturn(true);

		$redis = new Redis($client);

		$redis->putIfNotExists('foo', 123, 3600);

		//

		$client = $this->getRedisClient(true);

		$lua = "return redis.call('exists', KEYS[1]) == 0 and redis.call('setex', KEYS[1], ARGV[1], ARGV[2])";

		$client->shouldReceive('eval')->once()->with($lua, 1, 'foo', 3600, serialize('foo'))->andReturn(true);

		$redis = new Redis($client);

		$redis->putIfNotExists('foo', 'foo', 3600);
	}

	/**
	 *
	 */
	public function testIncrement(): void
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
	public function testDecrement(): void
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
	public function testHas(): void
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
	public function testGet(): void
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
	public function testRemove(): void
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
	public function testClear(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('keys')->once()->with('*')->andReturn(['foo', 'bar']);

		$client->shouldReceive('del')->once()->with('foo', 'bar')->andReturn(2);

		$redis = new Redis($client);

		$redis->clear();
	}

	/**
	 *
	 */
	public function testClearWithPrefix(): void
	{
		$client = $this->getRedisClient();

		$client->shouldReceive('keys')->once()->with('prefix.*')->andReturn(['prefix.foo', 'prefix.bar']);

		$client->shouldReceive('del')->once()->with('prefix.foo', 'prefix.bar')->andReturn(2);

		$redis = new Redis($client);

		$redis->setPrefix('prefix');

		$redis->clear();
	}
}
