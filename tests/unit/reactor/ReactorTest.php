<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\reactor\Reactor;

/**
 * @group unit
 */
class ReactorTest extends PHPUnit_Framework_TestCase
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
	public function testNoInput()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn(null);

		$input->shouldReceive('getArgument')->once()->with('option')->andReturn(null);

		//

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('write')->times(8)->with(PHP_EOL);

		$output->shouldReceive('writeLn')->once()->with('logo');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Usage:</yellow>');

		$output->shouldReceive('writeLn')->once()->with('php reactor [command] [arguments] [options]');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Global options:</yellow>');

$optionsTable = <<<EOF
------------------------------------------------------
| <green>Option</green> | <green>Description</green> |
------------------------------------------------------
| --option              | option description         |
------------------------------------------------------

EOF;
		$output->shouldReceive('write')->once()->with($optionsTable, 1);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

$commandsTable = <<<EOF
-------------------------------------------------------
| <green>Command</green> | <green>Description</green> |
-------------------------------------------------------
| foo                    | foo description            |
-------------------------------------------------------

EOF;

		$output->shouldReceive('write')->once()->with($commandsTable, 1);

		//

		$container = Mockery::mock('mako\syringe\Container');

		$command = Mockery::mock('mako\reactor\Command');

		$command->shouldReceive('getCommandDescription')->once()->andReturn('foo description');

		$container->shouldReceive('get')->once()->andReturn($command);

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->setLogo('logo');

		$reactor->registerCustomOption('option', 'option description', function(){});

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$reactor->run();
	}

	/**
	 *
	 */
	public function testUknownCommand()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('foobar');

		//

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->times(3);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('writeLn')->once()->with('<red>Unknown command [ foobar ].</red>');

		//

		$container = Mockery::mock('mako\syringe\Container');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->run();
	}

	/**
	 *
	 */
	public function testCommand()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('foo');

		$input->shouldReceive('getArguments')->once()->andReturn(['reactor', 'foo']);

		//

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->times(2);

		//

		$container = Mockery::mock('mako\syringe\Container');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		$dispatcher->shouldReceive('dispatch')->once()->with('mako\tests\unit\reactor\Foo', ['reactor', 'foo']);

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$reactor->run();
	}

	/**
	 *
	 */
	public function testCommandWithCustomOption()
	{
		$closure = function(){};

		//
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('option')->andReturn(true);

		$input->shouldReceive('getArguments')->once()->andReturn(['reactor', 'foo']);

		//

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->times(2);

		//

		$container = Mockery::mock('mako\syringe\Container');

		$container->shouldReceive('call')->once()->with($closure, ['option' => true]);

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		$dispatcher->shouldReceive('dispatch')->once()->with('mako\tests\unit\reactor\Foo', ['reactor', 'foo']);

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCustomOption('option', 'option description', $closure);

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$reactor->run();
	}
}