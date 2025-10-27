<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cache\simple;

use DateInterval;
use Generator;
use mako\cache\simple\SimpleCache;
use mako\cache\stores\StoreInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;

#[Group('unit')]
class SimpleCacheTest extends TestCase
{
	use MockeryPHPUnitIntegration;

	/**
	 *
	 */
	public function testSetWithInvalidKey(): void
	{
		$this->expectException(InvalidArgumentException::class);

		/** @var StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$cache = new SimpleCache($store);

		$cache->set('@foo', 'bar');
	}

	/**
	 *
	 */
	public function testSetWithValidKey(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('put')->once()->with('foo', 'bar', 0)->andReturn(true);

		$cache = new SimpleCache($store);

		$this->assertTrue($cache->set('foo', 'bar'));
	}

	/**
	 *
	 */
	public function testSetWithValidKeyAndIntegerTTL(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('put')->once()->with('foo', 'bar', 123)->andReturn(true);

		$cache = new SimpleCache($store);

		$this->assertTrue($cache->set('foo', 'bar', 123));
	}

	/**
	 *
	 */
	public function testSetWithValidKeyAndDateIntervalTTL(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('put')->once()->with('foo', 'bar', 123)->andReturn(true);

		$cache = new SimpleCache($store);

		$this->assertTrue($cache->set('foo', 'bar', new DateInterval('PT123S')));
	}

	/**
	 *
	 */
	public function testGetWithInvalidKey(): void
	{
		$this->expectException(InvalidArgumentException::class);

		/** @var StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$cache = new SimpleCache($store);

		$cache->get('@foo');
	}

	/**
	 *
	 */
	public function testGetWithValidKey(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('get')->once()->with('foo')->andReturn('bar');

		$store->shouldReceive('get')->once()->with('bar')->andReturn(null);

		$store->shouldReceive('get')->once()->with('baz')->andReturn(null);

		$cache = new SimpleCache($store);

		$this->assertSame('bar', $cache->get('foo'));

		$this->assertNull($cache->get('bar'));

		$this->assertSame('baz', $cache->get('baz', 'baz'));
	}

	/**
	 *
	 */
	public function testHasWithInvalidKey(): void
	{
		$this->expectException(InvalidArgumentException::class);

		/** @var StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$cache = new SimpleCache($store);

		$cache->has('@foo');
	}

	/**
	 *
	 */
	public function testHasWithValidKey(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('has')->once()->with('foo')->andReturn(true);

		$store->shouldReceive('has')->once()->with('bar')->andReturn(false);

		$cache = new SimpleCache($store);

		$this->assertTrue($cache->has('foo'));

		$this->assertFalse($cache->has('bar'));
	}

	/**
	 *
	 */
	public function testDeleteWithInvalidKey(): void
	{
		$this->expectException(InvalidArgumentException::class);

		/** @var StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$cache = new SimpleCache($store);

		$cache->delete('@foo');
	}

	/**
	 *
	 */
	public function testDeleteWithValidKey(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('remove')->once()->with('foo')->andReturn(true);

		$store->shouldReceive('remove')->once()->with('bar')->andReturn(false);

		$cache = new SimpleCache($store);

		$this->assertTrue($cache->delete('foo'));

		$this->assertFalse($cache->delete('bar'));
	}

	/**
	 *
	 */
	public function testSetMultipleWithValidValues(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('put')->once()->with('foo', 'bar', 0)->andReturn(true);

		$store->shouldReceive('put')->once()->with('bar', 'foo', 0)->andReturn(true);

		$cache = new SimpleCache($store);

		$this->assertTrue($cache->setMultiple(['foo' => 'bar', 'bar' => 'foo']));
	}

	/**
	 *
	 */
	public function testSetMultipleWithValidGeneratorValues(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('put')->once()->with('foo', 'bar', 0)->andReturn(true);

		$store->shouldReceive('put')->once()->with('bar', 'foo', 0)->andReturn(true);

		$cache = new SimpleCache($store);

		$generator = function (): Generator {
			yield 'foo' => 'bar';
			yield 'bar' => 'foo';
		};

		$this->assertTrue($cache->setMultiple($generator()));
	}

	/**
	 *
	 */
	public function testGetMultipleWithValidKeys(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('get')->once()->with('foo')->andReturn('bar');

		$store->shouldReceive('get')->once()->with('bar')->andReturn('foo');

		$store->shouldReceive('get')->once()->with('baz')->andReturn(null);

		$cache = new SimpleCache($store);

		$values = $cache->getMultiple(['foo', 'bar', 'baz'], 'default');

		$this->assertIsArray($values);

		$this->assertSame('bar', $values['foo']);

		$this->assertSame('foo', $values['bar']);

		$this->assertSame('default', $values['baz']);
	}

	/**
	 *
	 */
	public function testGetMultipleWithValidGeneratorKeys(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('get')->once()->with('foo')->andReturn('bar');

		$store->shouldReceive('get')->once()->with('bar')->andReturn('foo');

		$store->shouldReceive('get')->once()->with('baz')->andReturn(null);

		$cache = new SimpleCache($store);

		$generator = function (): Generator {
			yield 'foo';
			yield 'bar';
			yield 'baz';
		};

		$values = $cache->getMultiple($generator(), 'default');

		$this->assertIsArray($values);

		$this->assertSame('bar', $values['foo']);

		$this->assertSame('foo', $values['bar']);

		$this->assertSame('default', $values['baz']);
	}

	/**
	 *
	 */
	public function testDeleteMultipleWithValidKeys(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('remove')->once()->with('foo')->andReturn(true);

		$store->shouldReceive('remove')->once()->with('bar')->andReturn(true);

		$store->shouldReceive('remove')->once()->with('baz')->andReturn(true);

		$cache = new SimpleCache($store);

		$this->assertTrue($cache->deleteMultiple(['foo', 'bar', 'baz'], 'default'));
	}

	/**
	 *
	 */
	public function testDeleteMultipleWithValidGeneratorKeys(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('remove')->once()->with('foo')->andReturn(true);

		$store->shouldReceive('remove')->once()->with('bar')->andReturn(true);

		$store->shouldReceive('remove')->once()->with('baz')->andReturn(true);

		$cache = new SimpleCache($store);

		$generator = function (): Generator {
			yield 'foo';
			yield 'bar';
			yield 'baz';
		};

		$this->assertTrue($cache->deleteMultiple($generator(), 'default'));
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		/** @var Mockery\MockInterface|StoreInterface $store */
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('clear')->once()->andReturn(true);

		$store->shouldReceive('clear')->once()->andReturn(false);

		$cache = new SimpleCache($store);

		$this->assertTrue($cache->clear());

		$this->assertFalse($cache->clear());
	}
}
