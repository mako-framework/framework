<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
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

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn([]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$container->shouldReceive('call')->once()->with([$command, 'execute'], ['foo', 'bar']);

		$dispatcher = new Dispatcher($container);

		$dispatcher->dispatch('foo\bar\Command', ['foo', 'bar']);
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

		$dispatcher->dispatch('foo\bar\Command', ['foo', 'bar']);
	}

	/**
	 * @expectedException \mako\reactor\exceptions\MissingArgumentException
	 */
	public function testDispatchWithMissingRequiredArguments()
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(true);

		$command->shouldReceive('getCommandArguments')->once()->andReturn(['arg2' => ['optional' => false]]);

		$command->shouldReceive('getCommandOptions')->never();

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		$dispatcher->dispatch('foo\bar\Command', []);
	}

	/**
	 * @expectedException \mako\reactor\exceptions\MissingOptionException
	 */
	public function testDispatchWithMissingRequiredOptions()
	{
		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(true);

		$command->shouldReceive('getCommandArguments')->once()->andReturn([]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn(['foo' => ['optional' => false]]);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		$dispatcher->dispatch('foo\bar\Command', []);
	}
}