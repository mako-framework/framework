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

class BarCommand implements CommandInterface
{
	public $foo;

	public function __construct($foo)
	{
		$this->foo = $foo;
	}
}

class BarHandler implements CommandHandlerInterface
{
	public function handle(CommandInterface $command)
	{
		return $command->foo;
	}
}

class FooMiddleware
{
	public function execute(CommandInterface $command, $next)
	{
		return 'foo_before_' . $next($command) . '_foo_after';
	}
}

class BarMiddleware
{
	public function execute(CommandInterface $command, $next)
	{
		return 'bar_before_' . $next($command) . '_bar_after';
	}
}

class Baz implements CommandInterface
{
	public $baz = 'baz';
}

class BazHandler implements CommandHandlerInterface
{
	public function handle(CommandInterface $command)
	{
		return $command->baz;
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
	 *
	 */

	public function testCommandWithoutSuffix()
	{
		$bus = new CommandBus;

		$handled = $bus->dispatch(Baz::class);

		$this->assertSame('baz', $handled);
	}

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

	/**
	 *
	 */

	public function testMiddleware()
	{
		$bus = new CommandBus;

		$bus->addMiddleware(FooMiddleware::class);
		$bus->addMiddleware(BarMiddleware::class);

		$handled = $bus->dispatch(new BarCommand('handled'));

		$this->assertSame('foo_before_bar_before_handled_bar_after_foo_after', $handled);

		//

		$bus = new CommandBus;

		$bus->addMiddleware(FooMiddleware::class, false);
		$bus->addMiddleware(BarMiddleware::class, false);

		$handled = $bus->dispatch(new BarCommand('handled'));

		$this->assertSame('bar_before_foo_before_handled_foo_after_bar_after', $handled);
	}

	/**
	 *
	 */

	public function testOneTimeMiddleware()
	{
		$bus = new CommandBus;

		$handled = $bus->dispatch(new BarCommand('handled'), [], [FooMiddleware::class, BarMiddleware::class]);

		$this->assertSame('foo_before_bar_before_handled_bar_after_foo_after', $handled);

		//

		$handled = $bus->dispatch(new BarCommand('handled'));

		$this->assertSame('handled', $handled);
	}
}
