<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use mako\database\connections\SQLServer as SQLServerConnection;
use mako\database\query\compilers\SQLServer as SQLServerCompiler;
use mako\database\query\helpers\HelperInterface;
use mako\database\query\Query;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class SQLServerCompilerTest extends TestCase
{
	/**
	 * @return Mockery\MockInterface|SQLServerConnection
	 */
	protected function getConnection()
	{
		/** @var Mockery\MockInterface|SQLServerConnection $connection */
		$connection = Mockery::mock(SQLServerConnection::class);

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock(HelperInterface::class));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function ($query) {
			return new SQLServerCompiler($query);
		});

		return $connection;
	}

	/**
	 *
	 */
	protected function getBuilder($table = 'foobar')
	{
		return (new Query($this->getConnection()))->table($table);
	}

	/**
	 *
	 */
	public function testBasicSelect(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar]', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimit(): void
	{
		$query = $this->getBuilder();

		$query->limit(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] ORDER BY (SELECT 0) OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimitAndOrder(): void
	{
		$query = $this->getBuilder();

		$query->orderBy('foo', 'DESC');

		$query->limit(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] ORDER BY [foo] DESC OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOffset(): void
	{
		$query = $this->getBuilder();

		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] ORDER BY (SELECT 0) OFFSET 10 ROWS', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithOffsetAndOrder(): void
	{
		$query = $this->getBuilder();

		$query->orderBy('foo', 'DESC');

		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] ORDER BY [foo] DESC OFFSET 10 ROWS', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimitAndOffset(): void
	{
		$query = $this->getBuilder();

		$query->limit(10);
		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] ORDER BY (SELECT 0) OFFSET 10 ROWS FETCH NEXT 10 ROWS ONLY', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimitOffsetAndOrder(): void
	{
		$query = $this->getBuilder();

		$query->orderBy('foo', 'DESC');

		$query->limit(10);
		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] ORDER BY [foo] DESC OFFSET 10 ROWS FETCH NEXT 10 ROWS ONLY', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithJSONColumn(): void
	{
		$query = $this->getBuilder();

		$query->select(['json->foo->0->bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_VALUE([json], \'lax $."foo"[0]."bar"\') FROM [foobar]', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->\'bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_VALUE([json], \'lax $."foo"[0]."\'\'bar"\') FROM [foobar]', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->"bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_VALUE([json], \'lax $."foo"[0]."\\\"bar"\') FROM [foobar]', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->bar as jsonvalue']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_VALUE([json], \'lax $."foo"[0]."bar"\') AS [jsonvalue] FROM [foobar]', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['foobar.json->foo->0->bar as jsonvalue']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT JSON_VALUE([foobar].[json], \'lax $."foo"[0]."bar"\') AS [jsonvalue] FROM [foobar]', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testUpdateWithJSONColumn(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->update(['data->foo->bar->0' => 1]);

		$this->assertEquals('UPDATE [foobar] SET [data] = JSON_MODIFY([data], \'lax $."foo"."bar"[0]\', JSON_QUERY(\'?\'))', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithExclusiveLock(): void
	{
		$query = $this->getBuilder();

		$query->lock();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WITH (UPDLOCK, ROWLOCK)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithSharedLock(): void
	{
		$query = $this->getBuilder();

		$query->lock(false);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WITH (HOLDLOCK, ROWLOCK)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithSharedLockMethod(): void
	{
		$query = $this->getBuilder();

		$query->sharedLock();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WITH (HOLDLOCK, ROWLOCK)', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithCustomLock(): void
	{
		$query = $this->getBuilder();

		$query->lock('CUSTOM LOCK');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] CUSTOM LOCK', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testBetweenDate(): void
	{
		$query = $this->getBuilder();

		$query->betweenDate('date', '2019-07-05', '2019-07-06');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE CAST([date] AS DATE) BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05', '2019-07-06'], $query['params']);
	}

	/**
	 *
	 */
	public function testOrBetweenDate(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->orBetweenDate('date', '2019-07-05', '2019-07-06');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE [foo] = ? OR CAST([date] AS DATE) BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['bar', '2019-07-05', '2019-07-06'], $query['params']);
	}

	/**
	 *
	 */
	public function testNotBetweenDate(): void
	{
		$query = $this->getBuilder();

		$query->notBetweenDate('date', '2019-07-05', '2019-07-06');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE CAST([date] AS DATE) NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05', '2019-07-06'], $query['params']);
	}

	/**
	 *
	 */
	public function testOrNotBetweenDate(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->orNotBetweenDate('date', '2019-07-05', '2019-07-06');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE [foo] = ? OR CAST([date] AS DATE) NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['bar', '2019-07-05', '2019-07-06'], $query['params']);
	}

	/**
	 *
	 */
	public function testWhereDate(): void
	{
		$query = $this->getBuilder();

		$query->whereDate('date', '=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE CAST([date] AS DATE) = ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '!=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE CAST([date] AS DATE) != ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<>', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE CAST([date] AS DATE) <> ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '>', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE CAST([date] AS DATE) > ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '>=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE CAST([date] AS DATE) >= ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE CAST([date] AS DATE) < ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE CAST([date] AS DATE) <= ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', 'LIKE', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE CAST([date] AS DATE) LIKE ?', $query['sql']);
		$this->assertEquals(['2019-07-05'], $query['params']);
	}

	/**
	 *
	 */
	public function testOrWhereDate(): void
	{
		$query = $this->getBuilder();

		$query->where('foo', '=', 'bar');
		$query->orWhereDate('date', '=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM [foobar] WHERE [foo] = ? OR CAST([date] AS DATE) = ?', $query['sql']);
		$this->assertEquals(['bar', '2019-07-05'], $query['params']);
	}

	/**
	 *
	 */
	public function testUpdateAndReturn(): void
	{
		$query = $this->getBuilder();

		$query->where('id', '=', 1);

		$query = $query->getCompiler()->updateAndReturn(['foo' => 'bar'], ['id', 'foo']);

		$this->assertEquals('UPDATE [foobar] SET [foo] = ? OUTPUT inserted.[id], inserted.[foo] WHERE [id] = ?', $query['sql']);
		$this->assertEquals(['bar', 1], $query['params']);
	}
}
