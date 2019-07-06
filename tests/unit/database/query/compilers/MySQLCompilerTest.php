<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use mako\database\connections\Connection;
use mako\database\query\helpers\HelperInterface;
use mako\database\query\Query;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class MySQLCompilerTest extends TestCase
{
	/**
	 *
	 */
	protected function getConnection()
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock(HelperInterface::class));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function($query)
		{
			return new \mako\database\query\compilers\MySQL($query);
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

		$this->assertEquals('SELECT * FROM `foobar`', $query['sql']);
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

		$this->assertEquals('SELECT `json`->>\'$."foo"[0]."bar"\' FROM `foobar`', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->"bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT `json`->>\'$."foo"[0]."\\\"bar"\' FROM `foobar`', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->\'bar']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT `json`->>\'$."foo"[0]."\'\'bar"\' FROM `foobar`', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['json->foo->0->bar as jsonvalue']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT `json`->>\'$."foo"[0]."bar"\' AS `jsonvalue` FROM `foobar`', $query['sql']);
		$this->assertEquals([], $query['params']);

		//

		$query = $this->getBuilder();

		$query->select(['foobar.json->foo->0->bar as jsonvalue']);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT `foobar`.`json`->>\'$."foo"[0]."bar"\' AS `jsonvalue` FROM `foobar`', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithExclusiveLock(): void
	{
		$query = $this->getBuilder();

		$query->lock();

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` FOR UPDATE', $query['sql']);
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

		$this->assertEquals('SELECT * FROM `foobar` LOCK IN SHARE MODE', $query['sql']);
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

		$this->assertEquals('SELECT * FROM `foobar` LOCK IN SHARE MODE', $query['sql']);
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

		$this->assertEquals('SELECT * FROM `foobar` CUSTOM LOCK', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testUpdateWithJSONColumn(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->update(['data->foo->bar->0' => 1]);

		$this->assertEquals('UPDATE `foobar` SET `data` = JSON_SET(`data`, \'$."foo"."bar"[0]\', CAST(? AS JSON))', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicInsertWithNoValues(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->insert([]);

		$this->assertEquals('INSERT INTO `foobar` () VALUES ()', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testWhereDate(): void
	{
		$query = $this->getBuilder();

		$query->whereDate('date', '=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00', '2019-07-05 23:59:59'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '!=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00', '2019-07-05 23:59:59'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<>', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00', '2019-07-05 23:59:59'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '>', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` > ?', $query['sql']);
		$this->assertEquals(['2019-07-05 23:59:59'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '>=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` >= ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` < ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` <= ?', $query['sql']);
		$this->assertEquals(['2019-07-05 23:59:59'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', 'LIKE', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE DATE(`date`) LIKE ?', $query['sql']);
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

		$this->assertEquals('SELECT * FROM `foobar` WHERE `foo` = ? OR `date` BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['bar', '2019-07-05 00:00:00', '2019-07-05 23:59:59'], $query['params']);
	}
}
