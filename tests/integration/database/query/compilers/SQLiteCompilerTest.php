<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\query\compilers;

use LogicException;
use mako\database\exceptions\NotFoundException;
use mako\database\query\Query;
use mako\database\query\Result;
use mako\database\query\ResultSet;
use mako\database\query\Subquery;
use mako\pagination\PaginationFactoryInterface;
use mako\pagination\PaginationInterface;
use mako\tests\integration\InMemoryDbTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

enum BackedUserEmailEnum: string
{
	case FOO = 'foo@example.org';
}

enum BackedUserIdEnum: int
{
	case FOO = 1;
}

enum UsernameEnum
{
	case foo;
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('integration')]
#[Group('integration:database')]
#[RequiresPhpExtension('pdo')]
#[RequiresPhpExtension('pdo_sqlite')]
class SQLiteCompilerTest extends InMemoryDbTestCase
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

		foreach ($results as $result) {
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
		/** @var Mockery\MockInterface|PaginationInterface $pagination */
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		/** @var Mockery\MockInterface|PaginationFactoryInterface $paginationFactory */
		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		/** @var Mockery\MockInterface|Query $query */
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
		/** @var Mockery\MockInterface|PaginationInterface $pagination */
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		/** @var Mockery\MockInterface|PaginationFactoryInterface $paginationFactory */
		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		/** @var Mockery\MockInterface|Query $query */
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
		/** @var Mockery\MockInterface|PaginationInterface $pagination */
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->never();

		$pagination->shouldReceive('offset')->never();

		/** @var Mockery\MockInterface|PaginationFactoryInterface $paginationFactory */
		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		/** @var Mockery\MockInterface|Query $query */
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
		/** @var Mockery\MockInterface|PaginationInterface $pagination */
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		/** @var Mockery\MockInterface|PaginationFactoryInterface $paginationFactory */
		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		/** @var Mockery\MockInterface|Query $query */
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
		/** @var Mockery\MockInterface|PaginationInterface $pagination */
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		/** @var Mockery\MockInterface|PaginationFactoryInterface $paginationFactory */
		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		/** @var Mockery\MockInterface|Query $query */
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
		/** @var Mockery\MockInterface|PaginationInterface $pagination */
		$pagination = Mockery::mock(PaginationInterface::class);

		$pagination->shouldReceive('limit')->once()->andReturn(10);

		$pagination->shouldReceive('offset')->once()->andReturn(0);

		/** @var Mockery\MockInterface|PaginationFactoryInterface $paginationFactory */
		$paginationFactory = Mockery::mock(PaginationFactoryInterface::class);

		$paginationFactory->shouldReceive('create')->once()->andReturn($pagination);

		/** @var Mockery\MockInterface|Query $query */
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

		$results = $query->table('users')->batch(function ($results): void {

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

		$results = $query->table('users')->where('id', '!=', 'foobar')->batch(function ($results): void {

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

		$result = $query->table('users')->select([new Subquery(function ($query): void {
			$query->table('users')->count();
		}, 'count')])->first();

		$this->assertInstanceOf('mako\database\query\Result', $result);

		$this->assertSame(1, count($this->connectionManager->getConnection()->getLog()));

		$this->assertEquals('SELECT (SELECT COUNT(*) FROM "users") AS "count" FROM "users" LIMIT 1', $this->connectionManager->getConnection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testBlob(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$blob = $query->table('blobs')->blob('blob');

		$this->assertIsResource($blob);

		$s = '';

		while (!feof($blob)) {
			$s .= fread($blob, 1);
		}

		$this->assertSame(36, strlen($s));

		$this->assertEquals('SELECT "blob" FROM "blobs" LIMIT 1', $this->connectionManager->getConnection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testBackedStringEnum(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$user = $query->table('users')->where('email', '=', BackedUserEmailEnum::FOO)->first();

		$this->assertSame(BackedUserEmailEnum::FOO->value, $user->email);
	}

	/**
	 *
	 */
	public function testBackedIntEnum(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$user = $query->table('users')->where('id', '=', BackedUserIdEnum::FOO)->first();

		$this->assertSame(BackedUserIdEnum::FOO->value, $user->id);
	}

	/**
	 *
	 */
	public function testEnum(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$user = $query->table('users')->where('username', '=', UsernameEnum::foo)->first();

		$this->assertSame(UsernameEnum::foo->name, $user->username);
	}

	/**
	 *
	 */
	public function testInsertAndReturn(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$inserted = $query->table('users')->insertAndReturn([
			'created_at' => '2025-05-28 23:23:00',
			'username'   => 'bax',
			'email'      => 'bax@example.org',
		], ['id', 'username']);

		$this->assertInstanceOf(Result::class, $inserted);

		$this->assertIsInt($inserted->id);
		$this->assertSame('bax', $inserted->username);

		$this->assertEquals('INSERT INTO "users" ("created_at", "username", "email") VALUES (\'2025-05-28 23:23:00\', \'bax\', \'bax@example.org\') RETURNING "id", "username"', $this->connectionManager->getConnection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testInsertMultipleAndReturn(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$inserted = $query->table('users')->insertMultipleAndReturn(
			['id', 'username'],
			[
				'created_at' => '2025-05-28 23:23:00',
				'username'   => 'bax',
				'email'      => 'bax@example.org',
			],
			[
				'created_at' => '2025-05-28 23:23:00',
				'username'   => 'fox',
				'email'      => 'fox@example.org',
			]
		);

		$this->assertInstanceOf(ResultSet::class, $inserted);

		$this->assertInstanceOf(Result::class, $inserted[0]);
		$this->assertIsInt($inserted[0]->id);
		$this->assertSame('bax', $inserted[0]->username);

		$this->assertInstanceOf(Result::class, $inserted[1]);
		$this->assertIsInt($inserted[1]->id);
		$this->assertSame('fox', $inserted[1]->username);

		$this->assertEquals('INSERT INTO "users" ("created_at", "username", "email") VALUES (\'2025-05-28 23:23:00\', \'bax\', \'bax@example.org\'), (\'2025-05-28 23:23:00\', \'fox\', \'fox@example.org\') RETURNING "id", "username"', $this->connectionManager->getConnection()->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testUpdateAndReturn(): void
	{
		$query = new Query($this->connectionManager->getConnection());

		$updated = $query->table('users')->where('id', '=', 1)->updateAndReturn(['username' => 'bax'], ['id', 'username']);

		$this->assertInstanceOf(ResultSet::class, $updated);
		$this->assertInstanceOf(Result::class, $updated[0]);

		$this->assertSame(1, $updated[0]->id);
		$this->assertSame('bax', $updated[0]->username);

		$this->assertEquals('UPDATE "users" SET "username" = \'bax\' WHERE "id" = 1 RETURNING "id", "username"', $this->connectionManager->getConnection()->getLog()[0]['query']);
	}
}
