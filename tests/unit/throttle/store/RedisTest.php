<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\throttle\store;

use DateTime;
use mako\redis\Redis;
use mako\tests\TestCase;
use mako\throttle\store\Redis as RedisStore;
use Mockery;
use Mockery\Generator\MockConfigurationBuilder;
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

		/** @var Mockery\MockInterface&Redis $redis */
		$redis = Mockery::mock(Redis::class, $mockConfigBuilder);

		$redis->shouldReceive('eval')->once();

		$redis->shouldReceive('incrBy')->once()->andReturn(42);

		$store = new RedisStore($redis);

		$this->assertSame(42, $store->increment('foobar', new DateTime));
	}
}
