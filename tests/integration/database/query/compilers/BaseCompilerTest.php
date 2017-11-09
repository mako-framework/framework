<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\query\compilers;

use mako\database\query\Query;
use mako\pagination\PaginationFactoryInterface;
use mako\pagination\PaginationInterface;

use BuilderTestCase;
use Mockery;

/**
 * @group integration
 * @group integration:database
 * @requires extension PDO
 * @requires extension pdo_sqlite
 */
class BaseCompilerTest extends BuilderTestCase
{
	/**
	 *
	 */
	public function testAllReturnType()
	{
		$query = new Query($this->connectionManager->connection());

		$results = $query->table('users')->all();

		$this->assertInstanceOf('mako\database\query\ResultSet', $results);

		$this->assertInstanceOf('mako\database\query\Result', $results[0]);

		$this->assertEquals('SELECT * FROM "users"', $this->connectionManager->connection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testFirstReturnType()
	{
		$query = new Query($this->connectionManager->connection());

		$result = $query->table('users')->first();

		$this->assertInstanceOf('mako\database\query\Result', $result);

		$this->assertEquals('SELECT * FROM "users" LIMIT 1', $this->connectionManager->connection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testYieldReturnType()
	{
		$query = new Query($this->connectionManager->connection());

		$results = $query->table('users')->yield();

		$this->assertInstanceOf('Generator', $results);

		foreach($results as $result)
		{
			$this->assertInstanceOf('mako\database\query\Result', $result);
		}

		$this->assertEquals('SELECT * FROM "users"', $this->connectionManager->connection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testPairs()
	{
		$query = new Query($this->connectionManager->connection());

		$results = $query->table('users')->ascending('id')->limit(2)->pairs('username', 'email');

		$this->assertEquals(['foo' => 'foo@example.org', 'bar' => 'bar@example.org'], $results);

		$this->assertEquals('SELECT "username", "email" FROM "users" ORDER BY "id" ASC LIMIT 2', $this->connectionManager->connection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testSelectWithPagination()
	{
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		$query = Mockery::mock(Query::class . '[getPaginationFactory]', [$this->connectionManager->connection()]);

		$query->shouldReceive('getPaginationFactory')->once()->andReturn($paginationFactory);

		$query->table('users')->paginate();

		$this->assertEquals(2, count($this->connectionManager->connection()->getLog()));

		$this->assertEquals('SELECT COUNT(*) FROM "users"', $this->connectionManager->connection()->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" LIMIT 10 OFFSET 0', $this->connectionManager->connection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testSelectWithPaginationAndZeroResults()
	{
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		$query = Mockery::mock(Query::class . '[getPaginationFactory]', [$this->connectionManager->connection()]);

		$query->shouldReceive('getPaginationFactory')->once()->andReturn($paginationFactory);

		$query->table('users')->where('id', '=', 0)->paginate();

		$this->assertEquals(1, count($this->connectionManager->connection()->getLog()));

		$this->assertEquals('SELECT COUNT(*) FROM "users" WHERE "id" = 0', $this->connectionManager->connection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testSelectWithPaginationAndOrdering()
	{
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		$query = Mockery::mock(Query::class . '[getPaginationFactory]', [$this->connectionManager->connection()]);

		$query->shouldReceive('getPaginationFactory')->once()->andReturn($paginationFactory);

		$query->table('users')->ascending('id')->paginate();

		$this->assertEquals(2, count($this->connectionManager->connection()->getLog()));

		$this->assertEquals('SELECT COUNT(*) FROM "users"', $this->connectionManager->connection()->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC LIMIT 10 OFFSET 0', $this->connectionManager->connection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testSelectWithPaginationAndGrouping()
	{
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		$query = Mockery::mock(Query::class . '[getPaginationFactory]', [$this->connectionManager->connection()]);

		$query->shouldReceive('getPaginationFactory')->once()->andReturn($paginationFactory);

		$query->table('users')->select(['id', 'username'])->groupBy(['id', 'username'])->paginate();

		$this->assertEquals(2, count($this->connectionManager->connection()->getLog()));

		$this->assertEquals('SELECT COUNT(*) FROM (SELECT "id", "username" FROM "users" GROUP BY "id", "username") AS "count"', $this->connectionManager->connection()->getLog()[0]['query']);

		$this->assertEquals('SELECT "id", "username" FROM "users" GROUP BY "id", "username" LIMIT 10 OFFSET 0', $this->connectionManager->connection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testSelectWithPaginationAndDistinct()
	{
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		$query = Mockery::mock(Query::class . '[getPaginationFactory]', [$this->connectionManager->connection()]);

		$query->shouldReceive('getPaginationFactory')->once()->andReturn($paginationFactory);

		$query->table('users')->select(['id', 'username'])->distinct()->paginate();

		$this->assertEquals(2, count($this->connectionManager->connection()->getLog()));

		$this->assertEquals('SELECT COUNT(*) FROM (SELECT DISTINCT "id", "username" FROM "users") AS "count"', $this->connectionManager->connection()->getLog()[0]['query']);

		$this->assertEquals('SELECT DISTINCT "id", "username" FROM "users" LIMIT 10 OFFSET 0', $this->connectionManager->connection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testBatch()
	{
		$query = new Query($this->connectionManager->connection());

		$results = $query->table('users')->batch(function($results)
		{

		});

		$this->assertEquals('SELECT * FROM "users" LIMIT 1000', $this->connectionManager->connection()->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" LIMIT 1000 OFFSET 1000', $this->connectionManager->connection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testBatchWithCriteria()
	{
		$query = new Query($this->connectionManager->connection());

		$results = $query->table('users')->where('id', '!=', 'foobar')->batch(function($results)
		{

		});

		$this->assertEquals('SELECT * FROM "users" WHERE "id" != \'foobar\' LIMIT 1000', $this->connectionManager->connection()->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" WHERE "id" != \'foobar\' LIMIT 1000 OFFSET 1000', $this->connectionManager->connection()->getLog()[1]['query']);
	}
}
