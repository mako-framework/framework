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
use mako\reactor\CommandInterface;
use mako\reactor\Dispatcher;
use mako\reactor\Reactor;
use mako\syringe\Container;
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
	public function testNoInput(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		$input->shouldReceive('getArgumentParser')->andReturn($argvParser);

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn(null);

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('write')->times(6)->with(PHP_EOL);

		$output->shouldReceive('writeLn')->once()->with('logo');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Usage:</yellow>');

		$output->shouldReceive('writeLn')->once()->with('php reactor [command] [arguments] [options]');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Global arguments and options:</yellow>');

		$optionsTable = <<<'EOF'
		--------------------------------------------------------------------------------
		| <green>Name</green> | <green>Description</green>   | <green>Optional</green> |
		--------------------------------------------------------------------------------
		| command             | Command name                 | Yes                     |
		| --help              | Displays helpful information | Yes                     |
		| --mute              | Mutes all output             | Yes                     |
		--------------------------------------------------------------------------------

		EOF;

		$output->shouldReceive('write')->once()->with($optionsTable, 1);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

$commandsTable = <<<'EOF'
-------------------------------------------------------
| <green>Command</green> | <green>Description</green> |
-------------------------------------------------------
| foo                    | foo description            |
-------------------------------------------------------

EOF;

		$output->shouldReceive('write')->once()->with($commandsTable, 1);

		//

		$container = Mockery::mock(Container::class);

		$command = Mockery::mock(CommandInterface::class);

		$command->shouldReceive('getDescription')->once()->andReturn('foo description');

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		//

		$reactor = Mockery::mock(Reactor::class, [$input, $output, $container, $dispatcher])
		->makePartial()
		->shouldAllowMockingProtectedMethods();

		$reactor->shouldReceive('instantiateCommandWithoutConstructor')->once()->andReturn($command);

		$reactor->setLogo('logo');

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

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

		$input->shouldReceive('getArgumentParser')->andReturn($argvParser);

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn(null);

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(true);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('mute')->once();

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('write')->times(6)->with(PHP_EOL);

		$output->shouldReceive('writeLn')->once()->with('logo');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Usage:</yellow>');

		$output->shouldReceive('writeLn')->once()->with('php reactor [command] [arguments] [options]');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Global arguments and options:</yellow>');

$optionsTable = <<<'EOF'
--------------------------------------------------------------------------------
| <green>Name</green> | <green>Description</green>   | <green>Optional</green> |
--------------------------------------------------------------------------------
| command             | Command name                 | Yes                     |
| --help              | Displays helpful information | Yes                     |
| --mute              | Mutes all output             | Yes                     |
--------------------------------------------------------------------------------

EOF;
		$output->shouldReceive('write')->once()->with($optionsTable, 1);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

$commandsTable = <<<'EOF'
-------------------------------------------------------
| <green>Command</green> | <green>Description</green> |
-------------------------------------------------------
| foo                    | foo description            |
-------------------------------------------------------

EOF;

		$output->shouldReceive('write')->once()->with($commandsTable, 1);

		//

		$container = Mockery::mock(Container::class);

		$command = Mockery::mock(CommandInterface::class);

		$command->shouldReceive('getDescription')->once()->andReturn('foo description');

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		//

		$reactor = Mockery::mock(Reactor::class, [$input, $output, $container, $dispatcher])
		->makePartial()
		->shouldAllowMockingProtectedMethods();

		$reactor->shouldReceive('instantiateCommandWithoutConstructor')->once()->andReturn($command);

		$reactor->setLogo('logo');

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

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

		$input->shouldReceive('getArgumentParser')->andReturn($argvParser);

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('foobar');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('writeLn')->once()->with('<red>Unknown command [ foobar ].</red>');

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$exitCode = $reactor->run();

		$this->assertSame(1, $exitCode);
	}

	/**
	 *
	 */
	public function testUknownCommandWithSuggestion(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		$input->shouldReceive('getArgumentParser')->andReturn($argvParser);

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('sevrer');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->times(3);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('writeLn')->once()->with('<red>Unknown command [ sevrer ]. Did you mean [ server ]?</red>');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

		//

		$command = Mockery::mock(CommandInterface::class);

		$command->shouldReceive('getDescription')->once()->andReturn('server description');

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

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
	public function testUknownCommandWithNoSuggestion(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		$input->shouldReceive('getArgumentParser')->andReturn($argvParser);

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('sevrer');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->times(3);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('writeLn')->once()->with('<red>Unknown command [ sevrer ].</red>');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Available commands:</yellow>');

		//

		$command = Mockery::mock(CommandInterface::class);

		$command->shouldReceive('getDescription')->once()->andReturn('server description');

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

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
	public function testCommandWithInvalidArguments(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		$input->shouldReceive('getArgumentParser')->andReturn($argvParser);

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--help')->andReturn(false);

		$input->shouldReceive('getArguments')->once()->andReturn(['command' => 'foo']);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('errorLn')->once()->with('<red>Invalid argument [ bar ].</red>');

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		$dispatcher->shouldReceive('dispatch')->once()->with('mako\tests\unit\reactor\Foo', [])->andThrow(new InvalidArgumentException('Invalid argument [ bar ].'));

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$exitCode = $reactor->run();

		$this->assertSame(1, $exitCode);
	}

	/**
	 *
	 */
	public function testCommandWithInvalidInput(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		$input->shouldReceive('getArgumentParser')->andReturn($argvParser);

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--help')->andReturn(false);

		$input->shouldReceive('getArguments')->once()->andReturn(['command' => 'foo']);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('errorLn')->once()->with('<red>Unexpected value.</red>');

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		$dispatcher->shouldReceive('dispatch')->once()->with('mako\tests\unit\reactor\Foo', [])->andThrow(new UnexpectedValueException('Unexpected value.'));

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

		$exitCode = $reactor->run();

		$this->assertSame(1, $exitCode);
	}

	/**
	 *
	 */
	public function testCommand(): void
	{
		$argvParser = new ArgvParser([]);

		//

		$input = Mockery::mock(Input::class);

		$input->shouldReceive('getArgumentParser')->andReturn($argvParser);

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--help')->andReturn(false);

		$input->shouldReceive('getArguments')->once()->andReturn(['command' => 'foo', 'test' => 'bar']);

		//

		$output = Mockery::mock(Output::class);

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

		$dispatcher->shouldReceive('dispatch')->once()->with('mako\tests\unit\reactor\Foo', ['test' => 'bar']);

		//

		$reactor = new Reactor($input, $output, $container, $dispatcher);

		$reactor->registerCommand('foo', 'mako\tests\unit\reactor\Foo');

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

		$input->shouldReceive('getArgumentParser')->andReturn($argvParser);

		$input->shouldReceive('getArgument')->once()->with('command')->andReturn('foo');

		$input->shouldReceive('getArgument')->once()->with('--mute')->andReturn(false);

		$input->shouldReceive('getArgument')->once()->with('--help')->andReturn(true);

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('write')->times(5)->with(PHP_EOL);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Command:</yellow>');

		$output->shouldReceive('writeLn')->once()->with('php reactor foo');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Description:</yellow>');

		$output->shouldReceive('writeLn')->once()->with('Command description.');

		$output->shouldReceive('writeLn')->once()->with('<yellow>Arguments and options:</yellow>');

		$argumentsTable = <<<'EOF'
------------------------------------------------------------------------------
| <green>Name</green> | <green>Description</green> | <green>Optional</green> |
------------------------------------------------------------------------------
| argument            | Argument description.      | Yes                     |
| --option1           | Option description.        | Yes                     |
| -o | --option2      | Option description.        | No                      |
------------------------------------------------------------------------------

EOF;

		$output->shouldReceive('write')->once()->with($argumentsTable, 1);

		//

		$command = Mockery::mock(CommandInterface::class);

		$command->shouldReceive('getDescription')->once()->andReturn('Command description.');

		$command->shouldReceive('getArguments')->once()->andReturn
		([
			new Argument('argument', 'Argument description.', Argument::IS_OPTIONAL),
			new Argument('--option1', 'Option description.', Argument::IS_OPTIONAL),
			new Argument('-o|--option2', 'Option description.'),
		]);

		//

		$container = Mockery::mock(Container::class);

		//

		$dispatcher = Mockery::mock(Dispatcher::class);

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
