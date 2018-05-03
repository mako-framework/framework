<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor;

use mako\reactor\exceptions\InvalidArgumentException;
use mako\reactor\exceptions\InvalidOptionException;
use mako\reactor\exceptions\MissingArgumentException;
use mako\reactor\exceptions\MissingOptionException;
use mako\reactor\Reactor;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class ReactorTest extends TestCase
{
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

		$output->shouldReceive('write')->times(6)->with(PHP_EOL);

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

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('getCommandDescription')->once()->andReturn('foo description');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		//

		$reactor = Mockery::mock(Reactor::class, [$input, $output, $container, $dispatcher])
		->makePartial()
		->shouldAllowMockingProtectedMethods();

		$reactor->shouldReceive('instantiateCommandWithoutConstructor')->once()->andReturn($command);

		$reactor->setLogo('logo');

		$reactor->registerGlobalOption('option', 'option description', function() {});

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$exitCode = $reactor->run();

		$this->assertSame(0, $exitCode);
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

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('writeLn')->once()->with('<red>Unknown command [ foobar ].</red>');

		//

		$container = Mockery::mock('mako\syringe\Container');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$exitCode = $reactor->run();

		$this->assertSame(1, $exitCode);
	}

	/**
	 *
	 */
	public function testUknownCommandWithSuggestion()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('sevrer');

		//

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->times(3);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('writeLn')->once()->with('<red>Unknown command [ sevrer ]. Did you mean [ server ]?</red>');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

		//

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('getCommandDescription')->once()->andReturn('server description');

		//

		$container = Mockery::mock('mako\syringe\Container');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		//

		$reactor = Mockery::mock(Reactor::class, [$input, $output, $container, $dispatcher])
		->makePartial()
		->shouldAllowMockingProtectedMethods();

		$reactor->shouldReceive('instantiateCommandWithoutConstructor')->once()->andReturn($command);

		$reactor->registerCommand('server', 'foobar');

		$exitCode = $reactor->run();

		$this->assertSame(1, $exitCode);
	}

	/**
	 *
	 */
	public function testUknownCommandWithNoSuggestion()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('sevrer');

		//

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->times(3);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('writeLn')->once()->with('<red>Unknown command [ sevrer ].</red>');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

		//

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('getCommandDescription')->once()->andReturn('server description');

		//

		$container = Mockery::mock('mako\syringe\Container');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		//

		$reactor = Mockery::mock(Reactor::class, [$input, $output, $container, $dispatcher])
		->makePartial()
		->shouldAllowMockingProtectedMethods();

		$reactor->shouldReceive('instantiateCommandWithoutConstructor')->once()->andReturn($command);

		$reactor->registerCommand('foobar', 'foobar');

		$exitCode = $reactor->run();

		$this->assertSame(1, $exitCode);
	}

	/**
	 *
	 */
	public function testCommandWithInvalidArguments()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('help', false)->andReturn(false);

		$input->shouldReceive('getArguments')->once()->andReturn(['reactor', 'foo']);

		//

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('errorLn')->once()->with('<red>Invalid argument [ bar ].</red>');

		//

		$container = Mockery::mock('mako\syringe\Container');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		$dispatcher->shouldReceive('dispatch')->once()->with('mako\tests\unit\reactor\Foo', ['reactor', 'foo'])->andThrow(new InvalidArgumentException('foo', 'bar'));

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$exitCode = $reactor->run();

		$this->assertSame(1, $exitCode);
	}

	/**
	 *
	 */
	public function testCommandWithInvalidOptions()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('help', false)->andReturn(false);

		$input->shouldReceive('getArguments')->once()->andReturn(['reactor', 'foo']);

		//

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('errorLn')->once()->with('<red>Invalid option [ bar ].</red>');

		//

		$container = Mockery::mock('mako\syringe\Container');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		$dispatcher->shouldReceive('dispatch')->once()->with('mako\tests\unit\reactor\Foo', ['reactor', 'foo'])->andThrow(new InvalidOptionException('foo', 'bar'));

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$exitCode = $reactor->run();

		$this->assertSame(1, $exitCode);
	}

	/**
	 *
	 */
	public function testCommandWithInvalidOptionsAndSuggestion()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('help', false)->andReturn(false);

		$input->shouldReceive('getArguments')->once()->andReturn(['reactor', 'foo']);

		//

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('errorLn')->once()->with('<red>Invalid option [ bar ]. Did you mean [ baz ]?</red>');

		//

		$container = Mockery::mock('mako\syringe\Container');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		$dispatcher->shouldReceive('dispatch')->once()->with('mako\tests\unit\reactor\Foo', ['reactor', 'foo'])->andThrow(new InvalidOptionException('foo', 'bar', 'baz'));

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$exitCode = $reactor->run();

		$this->assertSame(1, $exitCode);
	}

	/**
	 *
	 */
	public function testCommandWithMissingRequiredArguments()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('help', false)->andReturn(false);

		$input->shouldReceive('getArguments')->once()->andReturn(['reactor', 'foo']);

		//

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('errorLn')->once()->with('<red>Missing required argument [ bar ].</red>');

		//

		$container = Mockery::mock('mako\syringe\Container');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		$dispatcher->shouldReceive('dispatch')->once()->with('mako\tests\unit\reactor\Foo', ['reactor', 'foo'])->andThrow(new MissingArgumentException('foo', 'bar'));

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$exitCode = $reactor->run();

		$this->assertSame(1, $exitCode);
	}

	/**
	 *
	 */
	public function testCommandWithMissingRequiredOption()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('help', false)->andReturn(false);

		$input->shouldReceive('getArguments')->once()->andReturn(['reactor', 'foo']);

		//

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('errorLn')->once()->with('<red>Missing required option [ bar ].</red>');

		//

		$container = Mockery::mock('mako\syringe\Container');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		$dispatcher->shouldReceive('dispatch')->once()->with('mako\tests\unit\reactor\Foo', ['reactor', 'foo'])->andThrow(new MissingOptionException('foo', 'bar'));

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$exitCode = $reactor->run();

		$this->assertSame(1, $exitCode);
	}

	/**
	 *
	 */
	public function testCommand()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('help', false)->andReturn(false);

		$input->shouldReceive('getArguments')->once()->andReturn(['reactor', 'foo']);

		//

		$output = Mockery::mock('mako\cli\output\Output');

		//

		$container = Mockery::mock('mako\syringe\Container');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		$dispatcher->shouldReceive('dispatch')->once()->with('mako\tests\unit\reactor\Foo', ['reactor', 'foo']);

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$exitCode = $reactor->run();

		$this->assertSame(0, $exitCode);
	}

	/**
	 *
	 */
	public function testCommandWithCustomOption()
	{
		$closure = function() {};

		//
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('help', false)->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('option')->andReturn(true);

		$input->shouldReceive('removeArgument')->once()->with('option');

		$input->shouldReceive('getArguments')->once()->andReturn(['reactor', 'foo']);

		//

		$output = Mockery::mock('mako\cli\output\Output');

		//

		$container = Mockery::mock('mako\syringe\Container');

		$container->shouldReceive('call')->once()->with($closure, ['option' => true]);

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		$dispatcher->shouldReceive('dispatch')->once()->with('mako\tests\unit\reactor\Foo', ['reactor', 'foo'])->andReturn(123);

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerGlobalOption('option', 'option description', $closure);

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$exitCode = $reactor->run();

		$this->assertSame(123, $exitCode);
	}

	/**
	 *
	 */
	public function testDisplayCommandHelp()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->once()->with(1)->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('help', false)->andReturn(true);

		//

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('write')->times(7)->with(PHP_EOL);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Command:</yellow>');

		$output->shouldReceive('writeLn')->once()->with('php reactor foo');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Description:</yellow>');

		$output->shouldReceive('writeLn')->once()->with('Command description.');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Arguments:</yellow>');

		$argumentsTable = <<<EOF
------------------------------------------------------------------------------
| <green>Name</green> | <green>Description</green> | <green>Optional</green> |
------------------------------------------------------------------------------
| arg2                | Argument description.      | true                    |
------------------------------------------------------------------------------

EOF;

		$output->shouldReceive('write')->once()->with($argumentsTable, 1);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Options:</yellow>');

		$optionsTable = <<<EOF
------------------------------------------------------------------------------
| <green>Name</green> | <green>Description</green> | <green>Optional</green> |
------------------------------------------------------------------------------
| option              | Option description.        | true                    |
------------------------------------------------------------------------------

EOF;

		$output->shouldReceive('write')->once()->with($optionsTable, 1);

		//

		$command = Mockery::mock('mako\reactor\CommandInterface');

		$command->shouldReceive('getCommandDescription')->once()->andReturn('Command description.');

		$command->shouldReceive('getCommandArguments')->once()->andReturn(['arg2' => ['optional' => true, 'description' => 'Argument description.']]);

		$command->shouldReceive('getCommandOptions')->once()->andReturn(['option' => ['optional' => true, 'description' => 'Option description.']]);

		//

		$container = Mockery::mock('mako\syringe\Container');

		//

		$dispatcher = Mockery::mock('mako\reactor\Dispatcher');

		//

		$reactor = Mockery::mock(Reactor::class, [$input, $output, $container, $dispatcher])
		->makePartial()
		->shouldAllowMockingProtectedMethods();

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$reactor->shouldReceive('instantiateCommandWithoutConstructor')->once()->with('mako\tests\unit\reactor\Foo')->andReturn($command);

		$exitCode = $reactor->run();

		$this->assertSame(0, $exitCode);
	}
}
