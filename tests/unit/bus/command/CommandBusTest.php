<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\bus\command;

use mako\bus\command\CommandBus;
use mako\bus\command\exceptions\CommandBusException;
use mako\syringe\Container;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Spy
{
	public $peek;
}

class CreateUserCommand
{
	public function __construct(
		public string $username
	) {
	}
}

class CreateUserHandler
{
	public function __construct(
		protected Spy $spy
	) {
	}

	public function __invoke(CreateUserCommand $createUser): void
	{
		$this->spy->peek = $createUser->username;
	}
}

function create_user_handler(CreateUserCommand $createUser): void
{
	assert($createUser->username === 'freost');
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('unit')]
class CommandBusTest extends TestCase
{
	/**
	 *
	 */
	public function testClassHandler(): void
	{
		$spy = new Spy;

		$createUserHandler = new CreateUserHandler($spy);

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(CreateUserHandler::class)->andReturn($createUserHandler);

		$bus = new CommandBus($container);

		$bus->registerHandler(CreateUserCommand::class, CreateUserHandler::class);

		$bus->handle(new CreateUserCommand('freost'));

		$this->assertSame('freost', $spy->peek);
	}

	/**
	 *
	 */
	public function testClosureHandler(): void
	{
		$spy = new Spy;

		$createUserHandler = function (CreateUserCommand $createUser) use ($spy): void {
			$spy->peek = $createUser->username;
		};

		$container = Mockery::mock(Container::class);

		$bus = new CommandBus($container);

		$bus->registerHandler(CreateUserCommand::class, $createUserHandler);

		$bus->handle(new CreateUserCommand('freost'));

		$this->assertSame('freost', $spy->peek);
	}

	/**
	 *
	 */
	public function testFunctionHandler(): void
	{
		$container = Mockery::mock(Container::class);

		$bus = new CommandBus($container);

		$bus->registerHandler(CreateUserCommand::class, __NAMESPACE__ . '\create_user_handler');

		$bus->handle(new CreateUserCommand('freost'));

		$this->assertTrue(true); // The test is done using php assertions
	}

	/**
	 *
	 */
	public function testMissingHandler(): void
	{
		$this->expectException(CommandBusException::class);
		$this->expectExceptionMessage('No handler has been registered for [ mako\tests\unit\bus\command\CreateUserCommand ] commands.');

		$container = Mockery::mock(Container::class);

		$bus = new CommandBus($container);

		$bus->handle(new CreateUserCommand('freost'));
	}
}
