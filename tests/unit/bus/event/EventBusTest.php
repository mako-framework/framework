<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\bus\event;

use mako\bus\event\EventBus;
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

class UserCreatedEvent
{
	public function __construct(
		public string $username
	) {
	}
}

class UserCreatedHandler
{
	public function __construct(
		protected Spy $spy
	) {
	}

	public function __invoke(UserCreatedEvent $userCreated): void
	{
		$this->spy->peek .= $userCreated->username;
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('unit')]
class EventBusTest extends TestCase
{
	/**
	 *
	 */
	public function testSingleClassHandler(): void
	{
		$spy = new Spy;

		$createUserHandler = new UserCreatedHandler($spy);

		/** @var Container|Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(UserCreatedHandler::class)->andReturn($createUserHandler);

		$bus = new EventBus($container);

		$bus->registerHandler(UserCreatedEvent::class, UserCreatedHandler::class);

		$bus->handle(new UserCreatedEvent('freost'));

		$this->assertSame('freost', $spy->peek);
	}

	/**
	 *
	 */
	public function testMultipleClassHandlers(): void
	{
		$spy = new Spy;

		$createUserHandler = new UserCreatedHandler($spy);

		/** @var Container|Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->times(2)->with(UserCreatedHandler::class)->andReturn($createUserHandler);

		$bus = new EventBus($container);

		$bus->registerHandler(UserCreatedEvent::class, UserCreatedHandler::class);
		$bus->registerHandler(UserCreatedEvent::class, UserCreatedHandler::class);

		$bus->handle(new UserCreatedEvent('freost'));

		$this->assertSame('freostfreost', $spy->peek);
	}

	/**
	 *
	 */
	public function testClosureHandler(): void
	{
		$spy = new Spy;

		$createUserHandler = function (UserCreatedEvent $createUser) use ($spy): void {
			$spy->peek = $createUser->username;
		};

		/** @var Container|Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		$bus = new EventBus($container);

		$bus->registerHandler(UserCreatedEvent::class, $createUserHandler);

		$bus->handle(new UserCreatedEvent('freost'));

		$this->assertSame('freost', $spy->peek);
	}

	/**
	 *
	 */
	public function testMissingHandler(): void
	{
		/** @var Container|Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		$container->shouldNotReceive('get');

		$bus = new EventBus($container);

		$bus->handle(new UserCreatedEvent('freost'));
	}
}
