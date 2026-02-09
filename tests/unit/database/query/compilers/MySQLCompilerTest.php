<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use mako\database\connections\MySQL as MySQLConnection;
use mako\database\query\compilers\MySQL as MySQLCompiler;
use mako\database\query\helpers\HelperInterface;
use mako\database\query\Query;
use mako\database\query\VectorMetric;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class MySQLCompilerTest extends TestCase
{
	/**
	 *
	 */
	protected function getConnection(): MockInterface&MySQLConnection
	{
		$connection = Mockery::mock(MySQLConnection::class);

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock(HelperInterface::class));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function ($query) {
			return new MySQLCompiler($query);
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
	public function testSelectWithLimitAndOffset(): void
	{
		$query = $this->getBuilder();

		$query->limit(10);
		$query->offset(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` LIMIT 10 OFFSET 10', $query['sql']);
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

		$this->assertEquals('SELECT * FROM `foobar` LIMIT 18446744073709551615 OFFSET 10', $query['sql']);
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
	public function testBasicCosineWhereVectorSimilarity(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->whereVectorSimilarity('embedding', [1, 2, 3, 4, 5], minSimilarity: 0.75)
		->getCompiler()->select();

		$this->assertEquals("SELECT * FROM `foobar` WHERE EXP(-DISTANCE(`embedding`, STRING_TO_VECTOR(?), 'COSINE')) >= ?", $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]', 0.75], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicEuclideanWhereVectorSimilarity(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->whereVectorSimilarity('embedding', [1, 2, 3, 4, 5], minSimilarity: 0.75, vectorMetric: VectorMetric::EUCLIDEAN)
		->getCompiler()->select();

		$this->assertEquals("SELECT * FROM `foobar` WHERE EXP(-DISTANCE(`embedding`, STRING_TO_VECTOR(?), 'EUCLIDEAN')) >= ?", $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]', 0.75], $query['params']);
	}

	/**
	 *
	 */
	public function testBetweenDate(): void
	{
		$query = $this->getBuilder();

		$query->betweenDate('date', '2019-07-05', '2019-07-06');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000', '2019-07-06 23:59:59.999999'], $query['params']);
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

		$this->assertEquals('SELECT * FROM `foobar` WHERE `foo` = ? OR `date` BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['bar', '2019-07-05 00:00:00.000000', '2019-07-06 23:59:59.999999'], $query['params']);
	}

	/**
	 *
	 */
	public function testNotBetweenDate(): void
	{
		$query = $this->getBuilder();

		$query->notBetweenDate('date', '2019-07-05', '2019-07-06');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000', '2019-07-06 23:59:59.999999'], $query['params']);
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

		$this->assertEquals('SELECT * FROM `foobar` WHERE `foo` = ? OR `date` NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['bar', '2019-07-05 00:00:00.000000', '2019-07-06 23:59:59.999999'], $query['params']);
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
		$this->assertEquals(['2019-07-05 00:00:00.000000', '2019-07-05 23:59:59.999999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '!=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000', '2019-07-05 23:59:59.999999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<>', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` NOT BETWEEN ? AND ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000', '2019-07-05 23:59:59.999999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '>', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` > ?', $query['sql']);
		$this->assertEquals(['2019-07-05 23:59:59.999999'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '>=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` >= ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` < ?', $query['sql']);
		$this->assertEquals(['2019-07-05 00:00:00.000000'], $query['params']);

		//

		$query = $this->getBuilder();

		$query->whereDate('date', '<=', '2019-07-05');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE `date` <= ?', $query['sql']);
		$this->assertEquals(['2019-07-05 23:59:59.999999'], $query['params']);

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
		$this->assertEquals(['bar', '2019-07-05 00:00:00.000000', '2019-07-05 23:59:59.999999'], $query['params']);
	}

	/**
	 *
	 */
	public function testUpdateWithWhereAndJoin(): void
	{
		$query = $this->getBuilder();

		$query->join('barfoo', 'barfoo.foobar_id', '=', 'foobar.id');

		$query->where('id', '=', 1);

		$query = $query->getCompiler()->update(['foo' => 'bar']);

		$this->assertEquals('UPDATE `foobar` INNER JOIN `barfoo` ON `barfoo`.`foobar_id` = `foobar`.`id` SET `foo` = ? WHERE `id` = ?', $query['sql']);
		$this->assertEquals(['bar', 1], $query['params']);
	}

	/**
	 *
	 */
	public function testUpdateWithWhereAndJoinAndAliases(): void
	{
		$query = $this->getBuilder('foobar as f');

		$query->join('barfoo as b', 'b.foobar_id', '=', 'f.id');

		$query->where('f.id', '=', 1);

		$query = $query->getCompiler()->update(['f.foo' => 'bar']);

		$this->assertEquals('UPDATE `foobar` AS `f` INNER JOIN `barfoo` AS `b` ON `b`.`foobar_id` = `f`.`id` SET `f`.`foo` = ? WHERE `f`.`id` = ?', $query['sql']);
		$this->assertEquals(['bar', 1], $query['params']);
	}

	/**
	 *
	 */
	public function testDeleteWithWhere(): void
	{
		$query = $this->getBuilder();

		$query->where('id', '=', 1);

		$query = $query->getCompiler()->delete();

		$this->assertEquals('DELETE FROM `foobar` WHERE `id` = ?', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testDeleteWithWhereAndJoin(): void
	{
		$query = $this->getBuilder();

		$query->join('barfoo', 'barfoo.foobar_id', '=', 'foobar.id');

		$query->where('id', '=', 1);

		$query = $query->getCompiler()->delete();

		$this->assertEquals('DELETE `foobar` FROM `foobar` INNER JOIN `barfoo` ON `barfoo`.`foobar_id` = `foobar`.`id` WHERE `id` = ?', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testDeleteWithWhereAndJoinAndMultipleTables(): void
	{
		$query = $this->getBuilder();

		$query->join('barfoo', 'barfoo.foobar_id', '=', 'foobar.id');

		$query->where('id', '=', 1);

		$query = $query->getCompiler()->delete(['foobar', 'barfoo']);

		$this->assertEquals('DELETE `foobar`, `barfoo` FROM `foobar` INNER JOIN `barfoo` ON `barfoo`.`foobar_id` = `foobar`.`id` WHERE `id` = ?', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testInsertOrUpdate(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->insertOrUpdate(['foo' => 'bar'], ['bar' => 'dupe']);

		$this->assertEquals('INSERT INTO `foobar` (`foo`) VALUES (?) ON DUPLICATE KEY UPDATE `bar` = ?', $query['sql']);
		$this->assertEquals(['bar', 'dupe'], $query['params']);
	}
}
