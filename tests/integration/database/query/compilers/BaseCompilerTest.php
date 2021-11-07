<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\query\compilers;

use LogicException;
use mako\database\exceptions\NotFoundException;
use mako\database\query\Query;
use mako\database\query\Subquery;
use mako\pagination\PaginationFactoryInterface;
use mako\pagination\PaginationInterface;
use mako\tests\integration\BuilderTestCase;
use Mockery;

/**
 * @group integration
 * @group integration:database
 * @requires extension PDO
 * @requires extension pdo_sqlite
 */
class BaseCompilerTest extends BuilderTestCase
{
	public function setUp(): void
	{
		parent::setUp();
	}

	/**
	 *
	 */
	public function testColumn(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$result = $query->table('users')->select(['id'])->where('id', '=', 1)->column();

		$this->assertEquals(1, $result);

		//

		$query = new Query($this->connectionManager->getConnection());

		$result = $query->table('users')->select(['id'])->where('id', '=', 0)->column();

		$this->assertNull($result);

		//

		$this->assertEquals('SELECT "id" FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection()->getLog()[0]['query']);

		$this->assertEquals('SELECT "id" FROM "users" WHERE "id" = 0 LIMIT 1', $this->connectionManager->getConnection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testAllReturnType(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$results = $query->table('users')->all();

		$this->assertInstanceOf('mako\database\query\ResultSet', $results);

		$this->assertInstanceOf('mako\database\query\Result', $results[0]);

		$this->assertEquals('SELECT * FROM "users"', $this->connectionManager->getConnection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testFirstReturnType(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$result = $query->table('users')->first();

		$this->assertInstanceOf('mako\database\query\Result', $result);

		$this->assertEquals('SELECT * FROM "users" LIMIT 1', $this->connectionManager->getConnection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testFirstOrThrow(): void
	{
		$this->expectException(NotFoundException::class);

		$query = new Query($this->connectionManager->getConnection());

		$query->table('users')->where('id', '=', 100)->firstOrThrow();
	}

	/**
	 *
	 */
	public function testFirstOrThrowWithCustomException(): void
	{
		$this->expectException(LogicException::class);

		$query = new Query($this->connectionManager->getConnection());

		$query->table('users')->where('id', '=', 100)->firstOrThrow(LogicException::class);
	}

	/**
	 *
	 */
	public function testYieldReturnType(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$results = $query->table('users')->yield();

		$this->assertInstanceOf('Generator', $results);

		foreach($results as $result)
		{
			$this->assertInstanceOf('mako\database\query\Result', $result);
		}

		$this->assertEquals('SELECT * FROM "users"', $this->connectionManager->getConnection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testPairs(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$results = $query->table('users')->ascending('id')->limit(2)->pairs('username', 'email');

		$this->assertEquals(['foo' => 'foo@example.org', 'bar' => 'bar@example.org'], $results);

		$this->assertEquals('SELECT "username", "email" FROM "users" ORDER BY "id" ASC LIMIT 2', $this->connectionManager->getConnection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testSelectWithPagination(): void
	{
		/** @var \mako\pagination\PaginationInterface|\Mockery\MockInterface $pagination */
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		/** @var \mako\pagination\PaginationFactoryInterface|\Mockery\MockInterface $paginationFactory */
		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		/** @var \mako\database\query\Query|\Mockery\MockInterface $query */
		$query = Mockery::mock(Query::class . '[getPaginationFactory]', [$this->connectionManager->getConnection()]);

		$query->shouldReceive('getPaginationFactory')->once()->andReturn($paginationFactory);

		$query->table('users')->paginate();

		$this->assertEquals(2, count($this->connectionManager->getConnection()->getLog()));

		$this->assertEquals('SELECT COUNT(*) FROM "users"', $this->connectionManager->getConnection()->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" LIMIT 10 OFFSET 0', $this->connectionManager->getConnection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testSelectWithUnionAndPagination(): void
	{
		/** @var \mako\pagination\PaginationInterface|\Mockery\MockInterface $pagination */
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		/** @var \mako\pagination\PaginationFactoryInterface|\Mockery\MockInterface $paginationFactory */
		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		/** @var \mako\database\query\Query|\Mockery\MockInterface $query */
		$query = Mockery::mock(Query::class . '[getPaginationFactory]', [$this->connectionManager->getConnection()]);

		$query->shouldReceive('getPaginationFactory')->once()->andReturn($paginationFactory);

		$query->table('users')->union()->table('users')->paginate();

		$this->assertEquals(2, count($this->connectionManager->getConnection()->getLog()));

		$this->assertEquals('SELECT COUNT(*) FROM (SELECT * FROM "users" UNION SELECT * FROM "users") AS "count"', $this->connectionManager->getConnection()->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" UNION SELECT * FROM "users" LIMIT 10 OFFSET 0', $this->connectionManager->getConnection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testSelectWithPaginationAndZeroResults(): void
	{
		/** @var \mako\pagination\PaginationInterface|\Mockery\MockInterface $pagination */
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->never();

		$pagination->shouldReceive('offset')->never();

		/** @var \mako\pagination\PaginationFactoryInterface|\Mockery\MockInterface $paginationFactory */
		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		/** @var \mako\database\query\Query|\Mockery\MockInterface $query */
		$query = Mockery::mock(Query::class . '[getPaginationFactory]', [$this->connectionManager->getConnection()]);

		$query->shouldReceive('getPaginationFactory')->once()->andReturn($paginationFactory);

		$query->table('users')->where('id', '=', 0)->paginate();

		$this->assertEquals(1, count($this->connectionManager->getConnection()->getLog()));

		$this->assertEquals('SELECT COUNT(*) FROM "users" WHERE "id" = 0', $this->connectionManager->getConnection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testSelectWithPaginationAndOrdering(): void
	{
		/** @var \mako\pagination\PaginationInterface|\Mockery\MockInterface $pagination */
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		/** @var \mako\pagination\PaginationFactoryInterface|\Mockery\MockInterface $paginationFactory */
		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		/** @var \mako\database\query\Query|\Mockery\MockInterface $query */
		$query = Mockery::mock(Query::class . '[getPaginationFactory]', [$this->connectionManager->getConnection()]);

		$query->shouldReceive('getPaginationFactory')->once()->andReturn($paginationFactory);

		$query->table('users')->ascending('id')->paginate();

		$this->assertEquals(2, count($this->connectionManager->getConnection()->getLog()));

		$this->assertEquals('SELECT COUNT(*) FROM "users"', $this->connectionManager->getConnection()->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC LIMIT 10 OFFSET 0', $this->connectionManager->getConnection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testSelectWithPaginationAndGrouping(): void
	{
		/** @var \mako\pagination\PaginationInterface|\Mockery\MockInterface $pagination */
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		/** @var \mako\pagination\PaginationFactoryInterface|\Mockery\MockInterface $paginationFactory */
		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		/** @var \mako\database\query\Query|\Mockery\MockInterface $query */
		$query = Mockery::mock(Query::class . '[getPaginationFactory]', [$this->connectionManager->getConnection()]);

		$query->shouldReceive('getPaginationFactory')->once()->andReturn($paginationFactory);

		$query->table('users')->select(['id', 'username'])->groupBy(['id', 'username'])->paginate();

		$this->assertEquals(2, count($this->connectionManager->getConnection()->getLog()));

		$this->assertEquals('SELECT COUNT(*) FROM (SELECT "id", "username" FROM "users" GROUP BY "id", "username") AS "count"', $this->connectionManager->getConnection()->getLog()[0]['query']);

		$this->assertEquals('SELECT "id", "username" FROM "users" GROUP BY "id", "username" LIMIT 10 OFFSET 0', $this->connectionManager->getConnection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testSelectWithPaginationAndDistinct(): void
	{
		/** @var \mako\pagination\PaginationInterface|\Mockery\MockInterface $pagination */
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		/** @var \mako\pagination\PaginationFactoryInterface|\Mockery\MockInterface $paginationFactory */
		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		/** @var \mako\database\query\Query|\Mockery\MockInterface $query */
		$query = Mockery::mock(Query::class . '[getPaginationFactory]', [$this->connectionManager->getConnection()]);

		$query->shouldReceive('getPaginationFactory')->once()->andReturn($paginationFactory);

		$query->table('users')->select(['id', 'username'])->distinct()->paginate();

		$this->assertEquals(2, count($this->connectionManager->getConnection()->getLog()));

		$this->assertEquals('SELECT COUNT(*) FROM (SELECT DISTINCT "id", "username" FROM "users") AS "count"', $this->connectionManager->getConnection()->getLog()[0]['query']);

		$this->assertEquals('SELECT DISTINCT "id", "username" FROM "users" LIMIT 10 OFFSET 0', $this->connectionManager->getConnection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testBatch(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$results = $query->table('users')->batch(function($results): void
		{

		});

		$this->assertEquals('SELECT * FROM "users" LIMIT 1000', $this->connectionManager->getConnection()->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" LIMIT 1000 OFFSET 1000', $this->connectionManager->getConnection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testBatchWithCriteria(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$results = $query->table('users')->where('id', '!=', 'foobar')->batch(function($results): void
		{

		});

		$this->assertEquals('SELECT * FROM "users" WHERE "id" != \'foobar\' LIMIT 1000', $this->connectionManager->getConnection()->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" WHERE "id" != \'foobar\' LIMIT 1000 OFFSET 1000', $this->connectionManager->getConnection()->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testSubQueryWithAggregate(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$result = $query->table('users')->select([new Subquery(function($query): void
		{
			$query->table('users')->count();
		}, 'count')])->first();

		$this->assertInstanceOf('mako\database\query\Result', $result);

		$this->assertSame(1, count($this->connectionManager->getConnection()->getLog()));

		$this->assertEquals('SELECT (SELECT COUNT(*) FROM "users") AS "count" FROM "users" LIMIT 1', $this->connectionManager->getConnection()->getLog()[0]['query']);
	}
}
