<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\reactor\Dispatcher;

/**
 * @group unit
 */
class DispatcherTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 *
	 */
	public function testDispatch()
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(true);

		$command->shouldReceive('isStrict')->once()->andReturn(false);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn([]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$container->shouldReceive('call')->once()->with([$command, 'execute'], ['foo', 'bar']);

		$dispatcher = new Dispatcher($container);

		$exitCode = $dispatcher->dispatch('foo\bar\Command', ['foo', 'bar']);

		$this->assertSame(0, $exitCode);
	}

	/**
	 *
	 */
	public function testDispatchWithExitCode()
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(true);

		$command->shouldReceive('isStrict')->once()->andReturn(false);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn([]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$container->shouldReceive('call')->once()->with([$command, 'execute'], ['foo', 'bar'])->andReturn(123);

		$dispatcher = new Dispatcher($container);

		$exitCode = $dispatcher->dispatch('foo\bar\Command', ['foo', 'bar']);

		$this->assertSame(123, $exitCode);
	}

	/**
	 *
	 */
	public function testDispatchWithNonIntExitCode()
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(true);

		$command->shouldReceive('isStrict')->once()->andReturn(false);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn([]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$container->shouldReceive('call')->once()->with([$command, 'execute'], ['foo', 'bar'])->andReturn('foobar');

		$dispatcher = new Dispatcher($container);

		$exitCode = $dispatcher->dispatch('foo\bar\Command', ['foo', 'bar']);

		$this->assertSame(0, $exitCode);
	}

	/**
	 *
	 */
	public function testDispatchNoExecution()
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(false);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		$exitCode = $dispatcher->dispatch('foo\bar\Command', ['foo', 'bar']);

		$this->assertSame(0, $exitCode);
	}

	/**
	 * @expectedException \mako\reactor\exceptions\InvalidArgumentException
	 * @expectedExceptionMessage mako\reactor\Dispatcher::checkForInvalidArguments(): Invalid argument [ arg3 ].
	 */
	public function testDispatchWithInvalidArguments()
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(true);

		$command->shouldReceive('isStrict')->once()->andReturn(true);

		$command->shouldReceive('getCommandArguments')->once()->andReturn(['arg2' => []]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn([]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		$dispatcher->dispatch('foo\bar\Command', ['arg3' => null]);
	}

	/**
	 * @expectedException \mako\reactor\exceptions\InvalidOptionException
	 * @expectedExceptionMessage mako\reactor\Dispatcher::checkForInvalidArguments(): Invalid option [ bar ].
	 */
	public function testDispatchWithInvalidOptions()
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(true);

		$command->shouldReceive('isStrict')->once()->andReturn(true);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn(['foo' => []]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		try
		{
			$dispatcher->dispatch('foo\bar\Command', ['bar' => null]);
		}
		catch(InvalidOptionException $e)
		{
			$this->assertNull($e->getSuggestion());

			throw $e;
		}
	}

	/**
	 * @expectedException \mako\reactor\exceptions\InvalidOptionException
	 * @expectedExceptionMessage mako\reactor\Dispatcher::checkForInvalidArguments(): Invalid option [ boo ].
	 */
	public function testDispatchWithInvalidOptionsAndSuggestion()
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(true);

		$command->shouldReceive('isStrict')->once()->andReturn(true);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn(['foo' => []]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		try
		{
			$dispatcher->dispatch('foo\bar\Command', ['boo' => null]);
		}
		catch(InvalidOptionException $e)
		{
			$this->assertEquals('foo', $e->getSuggestion());

			throw $e;
		}
	}

	/**
	 * @expectedException \mako\reactor\exceptions\MissingArgumentException
	 * @expectedExceptionMessage mako\reactor\Dispatcher::checkForMissingArgumentsOrOptions(): Missing required argument [ arg2 ].
	 */
	public function testDispatchWithMissingRequiredArguments()
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(true);

		$command->shouldReceive('isStrict')->once()->andReturn(false);

		$command->shouldReceive('getCommandArguments')->once()->andReturn(['arg2' => ['optional' => false]]);

		$command->shouldReceive('getCommandOptions')->never();

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		$dispatcher->dispatch('foo\bar\Command', []);
	}

	/**
	 * @expectedException \mako\reactor\exceptions\MissingOptionException
	 * @expectedExceptionMessage mako\reactor\Dispatcher::checkForMissingArgumentsOrOptions(): Missing required option [ foo ].
	 */
	public function testDispatchWithMissingRequiredOptions()
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(true);

		$command->shouldReceive('isStrict')->once()->andReturn(false);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn(['foo' => ['optional' => false]]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		$dispatcher->dispatch('foo\bar\Command', []);
	}
}
