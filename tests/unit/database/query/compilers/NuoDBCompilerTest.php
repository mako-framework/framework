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
class NuoDBCompilerTest extends TestCase
{
	/**
	 * @return \mako\database\connections\Connection|\Mockery\MockInterface
	 */
	protected function getConnection()
	{
		/** @var \mako\database\connections\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock(HelperInterface::class));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function ($query)
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
	public function testSelectWithLimit(): void
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
}
