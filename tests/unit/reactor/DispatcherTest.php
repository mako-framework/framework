<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor;

use mako\reactor\Dispatcher;
use mako\reactor\exceptions\InvalidArgumentException;
use mako\reactor\exceptions\InvalidOptionException;
use mako\reactor\exceptions\MissingArgumentException;
use mako\reactor\exceptions\MissingOptionException;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class DispatcherTest extends TestCase
{
	/**
	 *
	 */
	public function testDispatch(): void
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('isStrict')->once()->andReturn(false);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn([]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$container->shouldReceive('call')->once()->with([$command, 'execute'], ['foo', 'bar']);

		$dispatcher = new Dispatcher($container);

		$exitCode = $dispatcher->dispatch('foo\bar\Command', ['foo', 'bar'], []);

		$this->assertSame(0, $exitCode);
	}

	/**
	 *
	 */
	public function testDispatchWithSnakeCaseArguments(): void
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('isStrict')->once()->andReturn(false);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn([]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$container->shouldReceive('call')->once()->with([$command, 'execute'], ['fooSnake' => 1, 'barSnake' => 2]);

		$dispatcher = new Dispatcher($container);

		$exitCode = $dispatcher->dispatch('foo\bar\Command', ['foo_snake' => 1, 'bar_snake' => 2], []);

		$this->assertSame(0, $exitCode);
	}

	/**
	 *
	 */
	public function testDispatchWithExitCode(): void
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('isStrict')->once()->andReturn(false);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn([]);

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
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('isStrict')->once()->andReturn(false);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn([]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$container->shouldReceive('call')->once()->with([$command, 'execute'], ['foo', 'bar'])->andReturn('foobar');

		$dispatcher = new Dispatcher($container);

		$exitCode = $dispatcher->dispatch('foo\bar\Command', ['foo', 'bar'], []);

		$this->assertSame(0, $exitCode);
	}

	/**
	 *
	 */
	public function testDispatchWithInvalidArguments(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessage('Invalid argument [ arg3 ].');

		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('isStrict')->once()->andReturn(true);

		$command->shouldReceive('getCommandArguments')->once()->andReturn(['arg2' => []]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn([]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		$dispatcher->dispatch('foo\bar\Command', ['arg3' => null], []);
	}

	/**
	 *
	 */
	public function testDispatchWithInvalidOptions(): void
	{
		$this->expectException(InvalidOptionException::class);

		$this->expectExceptionMessage('Invalid option [ bar ].');

		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('isStrict')->once()->andReturn(true);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn(['foo' => []]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		try
		{
			$dispatcher->dispatch('foo\bar\Command', ['bar' => null], []);
		}
		catch(InvalidOptionException $e)
		{
			$this->assertNull($e->getSuggestion());

			throw $e;
		}
	}

	/**
	 *
	 */
	public function testDispatchWithInvalidOptionsAndSuggestion(): void
	{
		$this->expectException(InvalidOptionException::class);

		$this->expectExceptionMessage('Invalid option [ boo ].');

		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('isStrict')->once()->andReturn(true);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn(['foo' => []]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		try
		{
			$dispatcher->dispatch('foo\bar\Command', ['boo' => null], []);
		}
		catch(InvalidOptionException $e)
		{
			$this->assertEquals('foo', $e->getSuggestion());

			throw $e;
		}
	}

	/**
	 *
	 */
	public function testDispatchWithMissingRequiredArguments(): void
	{
		$this->expectException(MissingArgumentException::class);

		$this->expectExceptionMessage('Missing required argument [ arg2 ].');

		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('isStrict')->once()->andReturn(false);

		$command->shouldReceive('getCommandArguments')->once()->andReturn(['arg2' => ['optional' => false]]);

		$command->shouldReceive('getCommandOptions')->never();

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		$dispatcher->dispatch('foo\bar\Command', [], []);
	}

	/**
	 *
	 */
	public function testDispatchWithMissingRequiredOptions(): void
	{
		$this->expectException(MissingOptionException::class);

		$this->expectExceptionMessage('Missing required option [ foo ].');

		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('isStrict')->once()->andReturn(false);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn(['foo' => ['optional' => false]]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		$dispatcher->dispatch('foo\bar\Command', [], []);
	}
}
