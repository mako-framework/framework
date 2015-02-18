<?php

namespace mako\tests\unit\commander;

use Mockery as m;

use PHPUnit_Framework_TestCase;

use mako\commander\CommandBus;
use mako\commander\CommandInterface;
use mako\commander\CommandHandlerInterface;
use mako\commander\SelfHandlingCommandInterface;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class SelfHandlingCommand implements CommandInterface, SelfHandlingCommandInterface
{
	protected $foo;
	protected $bar;

	public function __construct($foo, $bar)
	{
		$this->foo = $foo;
		$this->bar = $bar;
	}

	public function handle()
	{
		return [$this->foo, $this->bar];
	}
}

class FooCommand implements CommandInterface
{
	public $foo;
	public $bar;

	public function __construct($foo, $bar)
	{
		$this->foo = $foo;
		$this->bar = $bar;
	}
}

class FooHandler implements CommandHandlerInterface
{
	public function handle(CommandInterface $command)
	{
		return [$command->foo, $command->bar];
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class CommandBusTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testSelfHandlingCommand()
	{
		$bus = new CommandBus;

		$handled = $bus->dispatch(SelfHandlingCommand::class, ['bar' => 'bar', 'foo' => 'foo']);

		$this->assertSame(['foo', 'bar'], $handled);
	}

	/**
	 *
	 */

	public function testSelfHandlingCommandInstanced()
	{
		$bus = new CommandBus;

		$handled = $bus->dispatch(new SelfHandlingCommand('foo', 'bar'));

		$this->assertSame(['foo', 'bar'], $handled);
	}

	/**
	 *
	 */

	public function testCommand()
	{
		$bus = new CommandBus;

		$handled = $bus->dispatch(FooCommand::class, ['bar' => 'bar', 'foo' => 'foo']);

		$this->assertSame(['foo', 'bar'], $handled);
	}

	/**
	 *
	 */

	public function testCommandInstanced()
	{
		$bus = new CommandBus;

		$handled = $bus->dispatch(new FooCommand('foo', 'bar'));

		$this->assertSame(['foo', 'bar'], $handled);
	}
}
