<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\throttle;

use DateInterval;
use DateTime;
use DateTimeInterface;
use mako\tests\TestCase;
use mako\throttle\context\ContextInterface;
use mako\throttle\RateLimiter;
use mako\throttle\stores\StoreInterface;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class RateLimiterTest extends TestCase
{
	/**
	 *
	 */
	public function testIsLimitReachedTrue(): void
	{
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('getHits')->once()->with('foo:bar')->andReturn(5);

		$context = Mockery::mock(ContextInterface::class);

		$context->shouldReceive('getIdentifier')->once()->andReturn('foo');

		$rateLimiter = new RateLimiter($store, $context);

		$this->assertTrue($rateLimiter->isLimitReached('bar', 5));
	}

	/**
	 *
	 */
	public function testIsLimitReachedFalse(): void
	{
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('getHits')->once()->with('foo:bar')->andReturn(4);

		$context = Mockery::mock(ContextInterface::class);

		$context->shouldReceive('getIdentifier')->once()->andReturn('foo');

		$rateLimiter = new RateLimiter($store, $context);

		$this->assertFalse($rateLimiter->isLimitReached('bar', 5));
	}

	/**
	 *
	 */
	public function testGetRemaining(): void
	{
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('getHits')->once()->with('foo:bar')->andReturn(4);

		$context = Mockery::mock(ContextInterface::class);

		$context->shouldReceive('getIdentifier')->once()->andReturn('foo');

		$rateLimiter = new RateLimiter($store, $context);

		$this->assertSame(1, $rateLimiter->getRemaining('bar', 5));
	}

	/**
	 *
	 */
	public function testGetRetryAfter(): void
	{
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('getExpiration')->once()->with('foo:bar')->andReturn(new DateTime);

		$context = Mockery::mock(ContextInterface::class);

		$context->shouldReceive('getIdentifier')->once()->andReturn('foo');

		$rateLimiter = new RateLimiter($store, $context);

		$this->assertInstanceOf(DateTimeInterface::class, $rateLimiter->getRetryAfter('bar'));
	}

	/**
	 *
	 */
	public function testGetRetryAfterWithMissingKey(): void
	{
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('getExpiration')->once()->with('foo:bar')->andReturn(null);

		$context = Mockery::mock(ContextInterface::class);

		$context->shouldReceive('getIdentifier')->once()->andReturn('foo');

		$rateLimiter = new RateLimiter($store, $context);

		$this->assertNull($rateLimiter->getRetryAfter('bar'));
	}

	/**
	 *
	 */
	public function testIncrement(): void
	{
		$expireAfter = new DateInterval('PT0S');

		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('increment')->once()->with('foo:bar', Mockery::type(DateTimeInterface::class))->andReturn(5);

		$context = Mockery::mock(ContextInterface::class);

		$context->shouldReceive('getIdentifier')->once()->andReturn('foo');

		$rateLimiter = new RateLimiter($store, $context);

		$this->assertSame(5, $rateLimiter->increment('bar', $expireAfter));
	}
}
