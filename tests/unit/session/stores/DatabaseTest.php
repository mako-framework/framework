<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\session\stores;

use mako\database\connections\Connection;
use mako\database\query\Query;
use mako\session\stores\Database;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class DatabaseTest extends TestCase
{
	/**
	 *
	 */
	public function getDatabaseConnection(): Connection&MockInterface
	{
		return Mockery::mock(Connection::class);
	}

	/**
	 *
	 */
	public function getQueryBuilder(): MockInterface&Query
	{
		return Mockery::mock(Query::class);
	}

	/**
	 *
	 */
	public function testWrite(): void
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->twice()->with('mako_sessions')->andReturn($builder);

		$builder->shouldReceive('where')->twice()->with('id', '=', 123)->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(1);

		$builder->shouldReceive('update')->once()->with(['data' => serialize(['foo' => 'bar']), 'expires' => time() + 123]);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->twice()->andReturn($builder);

		$store = new Database($connection, 'mako_sessions');

		$store->write(123, ['foo' => 'bar'], 123);

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->twice()->with('mako_sessions')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('id', '=', 123)->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(0);

		$builder->shouldReceive('insert')->once()->with(['id' => 123, 'data' => serialize(['foo' => 'bar']), 'expires' => time() + 123]);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->twice()->andReturn($builder);

		$store = new Database($connection, 'mako_sessions');

		$store->write(123, ['foo' => 'bar'], 123);
	}

	/**
	 *
	 */
	public function testRead(): void
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_sessions')->andReturn($builder);

		$builder->shouldReceive('select')->once()->with(['data'])->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('id', '=', 123)->andReturn($builder);

		$builder->shouldReceive('column')->once()->andReturn(serialize(['foo' => 'bar']));

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_sessions');

		$data = $store->read(123);

		$this->assertEquals(['foo' => 'bar'], $data);

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_sessions')->andReturn($builder);

		$builder->shouldReceive('select')->once()->with(['data'])->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('id', '=', 123)->andReturn($builder);

		$builder->shouldReceive('column')->once()->andReturn(null);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_sessions');

		$data = $store->read(123);

		$this->assertEquals([], $data);
	}

	/**
	 *
	 */
	public function testDelete(): void
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_sessions')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('id', '=', 123)->andReturn($builder);

		$builder->shouldReceive('delete')->once();

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_sessions');

		$store->delete(123);
	}

	/**
	 *
	 */
	public function testGc(): void
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_sessions')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('expires', '<', time())->andReturn($builder);

		$builder->shouldReceive('delete')->once();

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_sessions');

		$store->gc(123);
	}
}
