<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor;

use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\ArgvParser;
use mako\cli\input\arguments\exceptions\InvalidArgumentException;
use mako\cli\input\arguments\exceptions\UnexpectedValueException;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\reactor\attributes\CommandArguments;
use mako\reactor\attributes\CommandDescription;
use mako\reactor\attributes\CommandName;
use mako\reactor\Command;
use mako\reactor\Dispatcher;
use mako\reactor\Reactor;
use mako\syringe\Container;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

#[CommandName('foo')]
#[CommandDescription('Command description.')]
#[CommandArguments(
	new Argument('arg2', 'Argument description.', Argument::IS_OPTIONAL),
	new Argument('--option', 'Option description.'),
)]
class Foo extends Command
{
	public function execute(): void
	{

	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('unit')]
class ReactorTest extends TestCase
{
	/**
	 *
	 */
	public function testNoInput(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		(function () use ($argvParser): void {
			$this->argumentParser = $argvParser;
		})->bindTo($input, Input::class)();

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn(null);

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--non-interactive')->andReturn(false);

		//

		$output = Mockery::mock(Output::class);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('write')->times(8)->with(PHP_EOL);

		$output->shouldReceive('writeLn')->once()->with('logo');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Usage:</yellow>');

		$output->shouldReceive('writeLn')->once()->with('php reactor [command] [arguments] [options]');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Global arguments and options:</yellow>');

		$optionsTable = <<<'EOF'
		┏━━━━━━━━━━━━━━━━━━━━━┳━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┳━━━━━━━━━━━━━━━━━━━━━━━━━┓
		┃ <green>Name</green> ┃ <green>Description</green>   ┃ <green>Optional</green> ┃
		┣━━━━━━━━━━━━━━━━━━━━━╋━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━╋━━━━━━━━━━━━━━━━━━━━━━━━━┫
		┃ command             ┃ Command name                 ┃ Yes                     ┃
		┃ --help              ┃ Displays helpful information ┃ Yes                     ┃
		┃ --mute              ┃ Mutes all output             ┃ Yes                     ┃
		┃ --non-interactive   ┃ Disables interactive mode    ┃ Yes                     ┃
		┗━━━━━━━━━━━━━━━━━━━━━┻━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┻━━━━━━━━━━━━━━━━━━━━━━━━━┛

		EOF;

		$output->shouldReceive('write')->once()->with($optionsTable, 1);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

		$commandsTable = <<<'EOF'
		┏━━━━━━━━━━━━━━━━━━━━━━━━┳━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
		┃ <green>Command</green> ┃ <green>Description</green> ┃
		┣━━━━━━━━━━━━━━━━━━━━━━━━╋━━━━━━━━━━━━━━━━━━━━━━━━━━━━┫
		┃ foo                    ┃ Command description.       ┃
		┗━━━━━━━━━━━━━━━━━━━━━━━━┻━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

		EOF;

		$output->shouldReceive('write')->once()->with($commandsTable, 1);

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		//

		/** @var Mockery\MockInterface&Reactor $reactor */
		$reactor = Mockery::mock(Reactor::class, [$input, $output, $container, $dispatcher])
		->makePartial()
		->shouldAllowMockingProtectedMethods();

		//$reactor->shouldReceive('getCommandDescription')->once()->andReturn('foo description');

		$reactor->setLogo('logo');

		$reactor->registerCommand('foo', Foo::class);

		$exitCode = $reactor->run();

		$this->assertSame(0, $exitCode);
	}

	/**
	 *
	 */
	public function testNoInputWithMute(): void
	{
		$argvParser = new ArgvParser(['--mute']);

		//

		$input = Mockery::mock(Input::class);

		(function () use ($argvParser): void {
			$this->argumentParser = $argvParser;
		})->bindTo($input, Input::class)();

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn(null);

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(true);

		$input->shouldReceive('getArgument')->once()->with('--non-interactive')->andReturn(false);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('mute')->once();

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('write')->times(8)->with(PHP_EOL);

		$output->shouldReceive('writeLn')->once()->with('logo');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Usage:</yellow>');

		$output->shouldReceive('writeLn')->once()->with('php reactor [command] [arguments] [options]');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Global arguments and options:</yellow>');

		$optionsTable = <<<'EOF'
		┏━━━━━━━━━━━━━━━━━━━━━┳━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┳━━━━━━━━━━━━━━━━━━━━━━━━━┓
		┃ <green>Name</green> ┃ <green>Description</green>   ┃ <green>Optional</green> ┃
		┣━━━━━━━━━━━━━━━━━━━━━╋━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━╋━━━━━━━━━━━━━━━━━━━━━━━━━┫
		┃ command             ┃ Command name                 ┃ Yes                     ┃
		┃ --help              ┃ Displays helpful information ┃ Yes                     ┃
		┃ --mute              ┃ Mutes all output             ┃ Yes                     ┃
		┃ --non-interactive   ┃ Disables interactive mode    ┃ Yes                     ┃
		┗━━━━━━━━━━━━━━━━━━━━━┻━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┻━━━━━━━━━━━━━━━━━━━━━━━━━┛

		EOF;

		$output->shouldReceive('write')->once()->with($optionsTable, 1);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

		$commandsTable = <<<'EOF'
		┏━━━━━━━━━━━━━━━━━━━━━━━━┳━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
		┃ <green>Command</green> ┃ <green>Description</green> ┃
		┣━━━━━━━━━━━━━━━━━━━━━━━━╋━━━━━━━━━━━━━━━━━━━━━━━━━━━━┫
		┃ foo                    ┃ Command description.       ┃
		┗━━━━━━━━━━━━━━━━━━━━━━━━┻━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

		EOF;

		$output->shouldReceive('write')->once()->with($commandsTable, 1);

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		//

		/** @var Mockery\MockInterface&Reactor $reactor */
		$reactor = Mockery::mock(Reactor::class, [$input, $output, $container, $dispatcher])
		->makePartial()
		->shouldAllowMockingProtectedMethods();

		$reactor->setLogo('logo');

		$reactor->registerCommand('foo', Foo::class);

		$exitCode = $reactor->run();

		$this->assertSame(0, $exitCode);
	}

	/**
	 *
	 */
	public function testUknownCommand(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		(function () use ($argvParser): void {
			$this->argumentParser = $argvParser;
		})->bindTo($input, Input::class)();

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('foobar');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--non-interactive')->andReturn(false);

		//

		$output = Mockery::mock(Output::class);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('write')->times(2)->with(PHP_EOL);
		$output->shouldReceive('writeLn')->once()->with('<red>Unknown command [ foobar ].</red>');

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$exitCode = $reactor->run();

		$this->assertSame(127, $exitCode);
	}

	/**
	 *
	 */
	public function testUknownCommandWithSuggestion(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		(function () use ($argvParser): void {
			$this->argumentParser = $argvParser;
		})->bindTo($input, Input::class)();

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('sevrer');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--non-interactive')->andReturn(false);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->times(5);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('writeLn')->once()->with('<red>Unknown command [ sevrer ]. Did you mean [ server ]?</red>');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		//

		/** @var Mockery\MockInterface&Reactor $reactor */
		$reactor = Mockery::mock(Reactor::class, [$input, $output, $container, $dispatcher])
		->makePartial()
		->shouldAllowMockingProtectedMethods();

		$reactor->registerCommand('server', Foo::class);

		$exitCode = $reactor->run();

		$this->assertSame(127, $exitCode);
	}

	/**
	 *
	 */
	public function testUknownCommandWithNoSuggestion(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		(function () use ($argvParser): void {
			$this->argumentParser = $argvParser;
		})->bindTo($input, Input::class)();

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('sevrer');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--non-interactive')->andReturn(false);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->times(5);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('writeLn')->once()->with('<red>Unknown command [ sevrer ].</red>');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		//

		/** @var Mockery\MockInterface&Reactor $reactor */
		$reactor = Mockery::mock(Reactor::class, [$input, $output, $container, $dispatcher])
		->makePartial()
		->shouldAllowMockingProtectedMethods();

		$reactor->registerCommand('foobar', Foo::class);

		$exitCode = $reactor->run();

		$this->assertSame(127, $exitCode);
	}

	/**
	 *
	 */
	public function testCommandWithInvalidArguments(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		(function () use ($argvParser): void {
			$this->argumentParser = $argvParser;
		})->bindTo($input, Input::class)();

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--help')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--non-interactive')->andReturn(false);

		$input->shouldReceive('getArguments')->once()->andReturn(['command' => 'foo']);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->times(2)->with(PHP_EOL);

		$output->shouldReceive('errorLn')->once()->with('<red>Invalid argument [ bar ].</red>');

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		$dispatcher->shouldReceive('dispatch')->once()->with(Foo::class, [])->andThrow(new InvalidArgumentException('Invalid argument [ bar ].'));

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', Foo::class);

		$exitCode = $reactor->run();

		$this->assertSame(2, $exitCode);
	}

	/**
	 *
	 */
	public function testCommandWithInvalidInput(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		(function () use ($argvParser): void {
			$this->argumentParser = $argvParser;
		})->bindTo($input, Input::class)();

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--help')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--non-interactive')->andReturn(false);

		$input->shouldReceive('getArguments')->once()->andReturn(['command' => 'foo']);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->times(2)->with(PHP_EOL);

		$output->shouldReceive('errorLn')->once()->with('<red>Unexpected value.</red>');

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		$dispatcher->shouldReceive('dispatch')->once()->with(Foo::class, [])->andThrow(new UnexpectedValueException('Unexpected value.'));

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', Foo::class);

		$exitCode = $reactor->run();

		$this->assertSame(2, $exitCode);
	}

	/**
	 *
	 */
	public function testCommand(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		(function () use ($argvParser): void {
			$this->argumentParser = $argvParser;
		})->bindTo($input, Input::class)();

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--help')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--non-interactive')->andReturn(false);

		$input->shouldReceive('getArguments')->once()->andReturn(['command' => 'foo', 'test' => 'bar']);

		//

		$output = Mockery::mock(Output::class);

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		$dispatcher->shouldReceive('dispatch')->once()->with(Foo::class, ['test' => 'bar']);

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', Foo::class);

		$exitCode = $reactor->run();

		$this->assertSame(0, $exitCode);
	}

	/**
	 *
	 */
	public function testDisplayCommandHelp(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		(function () use ($argvParser): void {
			$this->argumentParser = $argvParser;
		})->bindTo($input, Input::class)();

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--help')->andReturn(true);

		$input->shouldReceive('getArgument')->once()->with('--non-interactive')->andReturn(false);

		//

		$output = Mockery::mock(Output::class);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('write')->times(7)->with(PHP_EOL);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Command:</yellow>');

		$output->shouldReceive('writeLn')->once()->with('php reactor foo');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Description:</yellow>');

		$output->shouldReceive('writeLn')->once()->with('Command description.');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Arguments and options:</yellow>');

		$argumentsTable = <<<'EOF'
		┏━━━━━━━━━━━━━━━━━━━━━┳━━━━━━━━━━━━━━━━━━━━━━━━━━━━┳━━━━━━━━━━━━━━━━━━━━━━━━━┓
		┃ <green>Name</green> ┃ <green>Description</green> ┃ <green>Optional</green> ┃
		┣━━━━━━━━━━━━━━━━━━━━━╋━━━━━━━━━━━━━━━━━━━━━━━━━━━━╋━━━━━━━━━━━━━━━━━━━━━━━━━┫
		┃ arg2                ┃ Argument description.      ┃ Yes                     ┃
		┃ --option            ┃ Option description.        ┃ No                      ┃
		┗━━━━━━━━━━━━━━━━━━━━━┻━━━━━━━━━━━━━━━━━━━━━━━━━━━━┻━━━━━━━━━━━━━━━━━━━━━━━━━┛

		EOF;

		$output->shouldReceive('write')->once()->with($argumentsTable, 1);

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		//

		/** @var Mockery\MockInterface&Reactor $reactor */
		$reactor = Mockery::mock(Reactor::class, [$input, $output, $container, $dispatcher])
		->makePartial()
		->shouldAllowMockingProtectedMethods();

		$reactor->registerCommand('foo', Foo::class);

		$exitCode = $reactor->run();

		$this->assertSame(0, $exitCode);
	}
}
