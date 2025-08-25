<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor;

use mako\reactor\CommandInterface;
use mako\reactor\Dispatcher;
use mako\syringe\Container;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class DispatcherTest extends TestCase
{
	/**
	 *
	 */
	public function testDispatch(): void
	{
		$container = Mockery::mock(Container::class);

		$command = Mockery::mock(CommandInterface::class);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$container->shouldReceive('call')->once()->with([$command, 'execute'], ['foo', 'bar']);

		$dispatcher = new Dispatcher($container);

		$exitCode = $dispatcher->dispatch('foo\bar\Command', ['foo', 'bar'], []);

		$this->assertSame(0, $exitCode);
	}

	/**
	 *
	 */
	public function testDispatchWithExitCode(): void
	{
		$container = Mockery::mock(Container::class);

		$command = Mockery::mock(CommandInterface::class);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$container->shouldReceive('call')->once()->with([$command, 'execute'], ['foo', 'bar'])->andReturn(123);

		$dispatcher = new Dispatcher($container);

		$exitCode = $dispatcher->dispatch('foo\bar\Command', ['foo', 'bar'], []);

		$this->assertSame(123, $exitCode);
	}

	/**
	 *
	 */
	public function testDispatchWithNonIntExitCode(): void
	{
		$container = Mockery::mock(Container::class);

		$command = Mockery::mock(CommandInterface::class);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$container->shouldReceive('call')->once()->with([$command, 'execute'], ['foo', 'bar'])->andReturn('foobar');

		$dispatcher = new Dispatcher($container);

		$exitCode = $dispatcher->dispatch('foo\bar\Command', ['foo', 'bar'], []);

		$this->assertSame(0, $exitCode);
	}
}
