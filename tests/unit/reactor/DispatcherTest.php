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
}