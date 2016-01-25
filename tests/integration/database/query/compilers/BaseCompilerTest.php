<?php

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

		$this->assertEquals('SELECT COUNT(*) FROM "users"', $this->connectionManager->connection()->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" LIMIT 10 OFFSET 0', $this->connectionManager->connection()->getLog()[1]['query']);
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

		$this->assertEquals('SELECT COUNT(*) FROM "users"', $this->connectionManager->connection()->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC LIMIT 10 OFFSET 0', $this->connectionManager->connection()->getLog()[1]['query']);
	}
}