<?php

namespace mako\tests\unit\reactor;

use Mockery as m;

use mako\reactor\Dispatcher;

use PHPUnit_Framework_TestCase;

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
		m::close();
	}

	/**
	 *
	 */

	public function testDispatch()
	{
		$container = m::mock('mako\syringe\Container');

		$command = m::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(true);

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
		$container = m::mock('mako\syringe\Container');

		$command = m::mock('mako\reactor\Command');

		$command->shouldReceive('shouldExecute')->once()->andReturn(false);

		$container->shouldReceive('get')->once()->with('foo\bar\Command')->andReturn($command);

		$dispatcher = new Dispatcher($container);

		$dispatcher->dispatch('foo\bar\Command', ['foo', 'bar']);
	}
}