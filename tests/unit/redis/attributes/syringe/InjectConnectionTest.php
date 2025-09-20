<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\redis\attributes\syringe;

use mako\redis\attributes\syringe\InjectConnection;
use mako\redis\ConnectionManager;
use mako\redis\Redis;
use mako\syringe\Container;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use ReflectionParameter;

#[Group('unit')]
class InjectConnectionTest extends TestCase
{
	/**
	 *
	 */
	public function testInjectConnectionWithNull(): void
	{
		$connection = Mockery::mock(Redis::class);

		$connectionManager = Mockery::mock(ConnectionManager::class);

		$connectionManager->shouldReceive('getConnection')->once()->with(null)->andReturn($connection);

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(ConnectionManager::class)->andReturn($connectionManager);

		$injector = new InjectConnection(null);

		$reflection = Mockery::mock(ReflectionParameter::class);

		$this->assertInstanceOf(Redis::class, $injector->getParameterValue($container, $reflection));
	}

	/**
	 *
	 */
	public function testInjectConnectionWithName(): void
	{
		$connection = Mockery::mock(Redis::class);

		$connectionManager = Mockery::mock(ConnectionManager::class);

		$connectionManager->shouldReceive('getConnection')->once()->with('foobar')->andReturn($connection);

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(ConnectionManager::class)->andReturn($connectionManager);

		$injector = new InjectConnection('foobar');

		$reflection = Mockery::mock(ReflectionParameter::class);

		$this->assertInstanceOf(Redis::class, $injector->getParameterValue($container, $reflection));
	}
}
