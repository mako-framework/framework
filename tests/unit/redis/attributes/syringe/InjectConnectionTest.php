<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\redis\attributes\syringe;

use mako\redis\attributes\syringe\InjectConnection;
use mako\redis\ConnectionManager;
use mako\redis\Redis;
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

		$injector = new InjectConnection(null, $connectionManager);

		$reflection = Mockery::mock(ReflectionParameter::class);

		$this->assertInstanceOf(Redis::class, $injector->getParameterValue($reflection));
	}

	/**
	 *
	 */
	public function testInjectConnectionWithName(): void
	{
		$connection = Mockery::mock(Redis::class);

		$connectionManager = Mockery::mock(ConnectionManager::class);

		$connectionManager->shouldReceive('getConnection')->once()->with('foobar')->andReturn($connection);

		$injector = new InjectConnection('foobar', $connectionManager);

		$reflection = Mockery::mock(ReflectionParameter::class);

		$this->assertInstanceOf(Redis::class, $injector->getParameterValue($reflection));
	}
}
