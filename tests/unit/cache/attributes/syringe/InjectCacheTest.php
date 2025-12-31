<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cache\attributes\syringe;

use mako\cache\attributes\syringe\InjectCache;
use mako\cache\CacheManager;
use mako\cache\stores\StoreInterface;
use mako\syringe\Container;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use ReflectionParameter;

#[Group('unit')]
class InjectCacheTest extends TestCase
{
	/**
	 *
	 */
	public function testInjectCacheWithNull(): void
	{
		$cache = Mockery::mock(StoreInterface::class);

		$cacheManager = Mockery::mock(CacheManager::class);

		$cacheManager->shouldReceive('getInstance')->once()->with(null)->andReturn($cache);

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(CacheManager::class)->andReturn($cacheManager);

		$injector = new InjectCache(null);

		$reflection = Mockery::mock(ReflectionParameter::class);

		$this->assertInstanceOf(StoreInterface::class, $injector->getParameterValue($container, $reflection));
	}

	/**
	 *
	 */
	public function testInjectCacheWithName(): void
	{
		$cache = Mockery::mock(StoreInterface::class);

		$cacheManager = Mockery::mock(CacheManager::class);

		$cacheManager->shouldReceive('getInstance')->once()->with('foobar')->andReturn($cache);

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(CacheManager::class)->andReturn($cacheManager);

		$injector = new InjectCache('foobar');

		$reflection = Mockery::mock(ReflectionParameter::class);

		$this->assertInstanceOf(StoreInterface::class, $injector->getParameterValue($container, $reflection));
	}
}
