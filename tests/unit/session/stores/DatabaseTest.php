<?php

namespace mako\tests\unit\session\stores;

use mako\session\stores\Database;

use \Mockery as m;

/**
 * @group unit
 */

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function getDatabaseConnection()
	{
		return m::mock('mako\database\Connection');
	}

	/**
	 *
	 */

	public function getQueryBuilder()
	{
		return m::mock('mako\database\query\Query');
	}

	/**
	 *
	 */

	public function testWrite()
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->twice()->with('mako_sessions')->andReturn($builder);

		$builder->shouldReceive('where')->twice()->with('id', '=', 123)->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(1);

		$builder->shouldReceive('update')->once()->with(['data' => serialize(['foo' => 'bar']), 'expires' => time() + 123]);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->twice()->andReturn($builder);

		$store = new Database($connection, 'mako_sessions');

		$store->write(123, ['foo' => 'bar'], 123);

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->twice()->with('mako_sessions')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('id', '=', 123)->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(0);

		$builder->shouldReceive('insert')->once()->with(['id' => 123, 'data' => serialize(['foo' => 'bar']), 'expires' => time() + 123]);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->twice()->andReturn($builder);

		$store = new Database($connection, 'mako_sessions');

		$store->write(123, ['foo' => 'bar'], 123);
	}

	/**
	 *
	 */

	public function testRead()
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_sessions')->andReturn($builder);

		$builder->shouldReceive('select')->once()->with(['data'])->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('id', '=', 123)->andReturn($builder);

		$builder->shouldReceive('column')->once()->andReturn(serialize(['foo' => 'bar']));

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_sessions');

		$data = $store->read(123);

		$this->assertEquals(['foo' => 'bar'], $data);

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_sessions')->andReturn($builder);

		$builder->shouldReceive('select')->once()->with(['data'])->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('id', '=', 123)->andReturn($builder);

		$builder->shouldReceive('column')->once()->andReturn(false);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_sessions');

		$data = $store->read(123);

		$this->assertEquals([], $data);
	}

	/**
	 *
	 */

	public function testDelete()
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_sessions')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('id', '=', 123)->andReturn($builder);

		$builder->shouldReceive('delete')->once();

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_sessions');

		$store->delete(123);
	}

	/**
	 *
	 */

	public function testGc()
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_sessions')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('expires', '<', time())->andReturn($builder);

		$builder->shouldReceive('delete')->once();

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_sessions');

		$store->gc(123);
	}
}