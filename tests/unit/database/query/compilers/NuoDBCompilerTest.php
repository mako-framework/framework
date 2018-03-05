<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use mako\database\query\Query;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class NuoDBCompilerTest extends TestCase
{
	/**
	 *
	 */
	protected function getConnection()
	{
		$connection = Mockery::mock('\mako\database\connections\Connection');

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock('\mako\database\query\helpers\HelperInterface'));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function($query)
		{
			return new \mako\database\query\compilers\NuoDB($query);
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
	public function testSelectWithLimit()
	{
		$query = $this->getBuilder();

		$query->limit(10);

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` LIMIT 10', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testSelectWithLimitAndOffset()
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
	public function testSelectWithExclusiveLock()
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
	public function testSelectWithSharedLock()
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
	public function testSelectWithCustomLock()
	{
		$query = $this->getBuilder();

		$query->lock('CUSTOM LOCK');

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` CUSTOM LOCK', $query['sql']);
		$this->assertEquals([], $query['params']);
	}
}
