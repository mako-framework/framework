<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\cache\stores;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\cache\stores\Database;

/**
 * @group unit
 */
class DatabaseTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 *
	 */
	public function getDatabaseConnection()
	{
		return Mockery::mock('mako\database\connections\Connection');
	}

	/**
	 *
	 */
	public function getQueryBuilder()
	{
		return Mockery::mock('mako\database\query\Query');
	}

	/**
	 *
	 */
	public function testPut()
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->twice()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('delete')->once()->andReturn(true);

		$builder->shouldReceive('insert')->with(['key' => 'foo', 'data' => serialize('foo'), 'lifetime' => 31556926 + time()])->once()->andReturn(true);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->twice()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$store->put('foo', 'foo');

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->twice()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('delete')->once()->andReturn(true);

		$builder->shouldReceive('insert')->with(['key' => 'foo', 'data' => serialize('foo'), 'lifetime' => 3600 + time()])->once()->andReturn(true);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->twice()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$store->put('foo', 'foo', 3600);
	}

	/**
	 *
	 */
	public function testHas()
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('where')->with('lifetime', '>', time())->once()->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(1);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->once()->andReturn($builder);

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

		$connection->shouldReceive('builder')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$has = $store->has('foo');

		$this->assertFalse($has);
	}

	/**
	 *
	 */
	public function testGet()
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('first')->once()->andReturn((object) ['key' => 'foo', 'data' => serialize('foo'), 'lifetime' => time() + 3600]);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$cached = $store->get('foo');

		$this->assertEquals('foo', $cached);

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('first')->once()->andReturn(false);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$cached = $store->get('foo');

		$this->assertEquals(false, $cached);

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->twice()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->twice()->andReturn($builder);

		$builder->shouldReceive('first')->once()->andReturn((object) ['key' => 'foo', 'data' => serialize('foo'), 'lifetime' => time() - 3600]);

		$builder->shouldReceive('delete')->once()->andReturn(true);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->twice()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$cached = $store->get('foo');

		$this->assertEquals(false, $cached);
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('delete')->once()->andReturn(1);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$deleted = $store->remove('foo');

		$this->assertTrue($deleted);

		//

		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('where')->with('key', '=', 'foo')->once()->andReturn($builder);

		$builder->shouldReceive('delete')->once()->andReturn(0);

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$deleted = $store->remove('foo');

		$this->assertFalse($deleted);
	}

	/**
	 *
	 */
	public function testClear()
	{
		$builder = $this->getQueryBuilder();

		$builder->shouldReceive('table')->once()->with('mako_cache')->andReturn($builder);

		$builder->shouldReceive('delete')->once();

		$connection = $this->getDatabaseConnection();

		$connection->shouldReceive('builder')->once()->andReturn($builder);

		$store = new Database($connection, 'mako_cache');

		$deleted = $store->clear();
	}
}