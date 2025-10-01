<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\attributes\syringe;

use mako\config\attributes\syringe\InjectConfig;
use mako\config\Config;
use mako\syringe\Container;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use ReflectionParameter;

#[Group('unit')]
class InjectConfigTest extends TestCase
{
	/**
	 *
	 */
	public function testInjectConnectionWithNull(): void
	{
		$config = Mockery::mock(Config::class);

		$config->shouldReceive('get')->with('key', null)->andReturn('foobar');

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(Config::class)->andReturn($config);

		$injector = new InjectConfig('key', null);

		$reflection = Mockery::mock(ReflectionParameter::class);

		$this->assertSame('foobar', $injector->getParameterValue($container, $reflection));
	}

	/**
	 *
	 */
	public function testInjectConnectionWithDefault(): void
	{
		$config = Mockery::mock(Config::class);

		$config->shouldReceive('get')->with('key', 'barfoo')->andReturn('foobar');

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(Config::class)->andReturn($config);

		$injector = new InjectConfig('key', 'barfoo');

		$reflection = Mockery::mock(ReflectionParameter::class);

		$this->assertSame('foobar', $injector->getParameterValue($container, $reflection));
	}
}
