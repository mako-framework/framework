<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\bus\query;

use mako\bus\query\exceptions\QueryBusException;
use mako\bus\query\QueryBus;
use mako\syringe\Container;
use mako\tests\TestCase;
use Mockery;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

readonly class GetUserQuery
{
	public function __construct(
		public string $username
	)
	{}
}

class GetUserHandler
{
	public function __invoke(GetUserQuery $getUser): GetUserQuery
	{
		return $getUser;
	}
}

function create_user_handler(GetUserQuery $getUser): GetUserQuery
{
	return $getUser;
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class QueryBusTest extends TestCase
{
	/**
	 *
	 */
	public function testClassHandler(): void
	{
		$query = new GetUserQuery('freost');

		/** @var Container|\Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(GetUserHandler::class)->andReturn(new GetUserHandler);

		$bus = new QueryBus($container);

		$bus->registerHandler(GetUserQuery::class, GetUserHandler::class);

		$result = $bus->handle($query);

		$this->assertSame($query, $result);
	}

	/**
	 *
	 */
	public function testClosureHandler(): void
	{
		$query = new GetUserQuery('freost');

		/** @var Container|\Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		$bus = new QueryBus($container);

		$bus->registerHandler(GetUserQuery::class, fn (GetUserQuery $getUser): GetUserQuery => $getUser);

		$result = $bus->handle($query);

		$this->assertSame($query, $result);
	}

	/**
	 *
	 */
	public function testFunctionHandler(): void
	{
		$query = new GetUserQuery('freost');

		/** @var Container|\Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		$bus = new QueryBus($container);

		$bus->registerHandler(GetUserQuery::class, __NAMESPACE__ . '\create_user_handler');

		$result = $bus->handle($query);

		$this->assertSame($query, $result);
	}

	/**
	 *
	 */
	public function testMissingHandler(): void
	{
		$this->expectException(QueryBusException::class);
		$this->expectExceptionMessage('No handler has been registered for [ mako\tests\unit\bus\query\GetUserQuery ] queries.');

		/** @var Container|\Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		$bus = new QueryBus($container);

		$bus->handle(new GetUserQuery('freost'));
	}
}
