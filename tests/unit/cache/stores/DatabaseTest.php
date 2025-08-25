<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cache\stores;

use mako\cache\stores\Database;
use mako\database\connections\Connection;
use mako\database\query\Query;
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
	public function testPut(): void
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->twice()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('delete')->once()->andReturn(true);

		$builder->shouldReceive('insert')->with(['key' => 'foo', 'data' => serialize('foo'), 'lifetime' => 31556926 + time()])->once()->andReturn(true);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->twice()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$store->put('foo', 'foo');

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->twice()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('delete')->once()->andReturn(true);

		$builder->shouldReceive('insert')->with(['key' => 'foo', 'data' => serialize('foo'), 'lifetime' => 3600 + time()])->once()->andReturn(true);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->twice()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$store->put('foo', 'foo', 3600);
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('where')->with('lifetime', '>', time())->once()->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(1);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$has = $store->has('foo');

		$this->assertTrue($has);

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('where')->with('lifetime', '>', time())->once()->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(0);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$has = $store->has('foo');

		$this->assertFalse($has);
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('first')->once()->andReturn((object) ['key' => 'foo', 'data' => serialize('foo'), 'lifetime' => time() + 3600]);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$cached = $store->get('foo');

		$this->assertEquals('foo', $cached);

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('first')->once()->andReturn(null);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$cached = $store->get('foo');

		$this->assertEquals(null, $cached);

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->twice()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->twice()->andReturn($builder);

		$builder->shouldReceive('first')->once()->andReturn((object) ['key' => 'foo', 'data' => serialize('foo'), 'lifetime' => time() - 3600]);

		$builder->shouldReceive('delete')->once()->andReturn(true);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->twice()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$cached = $store->get('foo');

		$this->assertEquals(null, $cached);
	}

	/**
	 *
	 */
	public function testRemove(): void
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('delete')->once()->andReturn(1);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$deleted = $store->remove('foo');

		$this->assertTrue($deleted);

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('delete')->once()->andReturn(0);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$deleted = $store->remove('foo');

		$this->assertFalse($deleted);
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('delete')->once();

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$store->clear();
	}
}
