<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use mako\database\connections\Connection;
use mako\database\query\compilers\MySQL;
use mako\database\query\compilers\Postgres;
use mako\database\query\helpers\HelperInterface;
use mako\database\query\Query;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ForCompilerTest extends TestCase
{
	/**
	 *
	 */
	protected function getConnection($compiler): Connection&MockInterface
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock(HelperInterface::class));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function ($query) use ($compiler) {
			return new $compiler($query);
		});

		return $connection;
	}

	/**
	 *
	 */
	protected function getBuilder($compiler, $table = 'foobar')
	{
		return (new Query($this->getConnection($compiler)))->table($table);
	}

	/**
	 *
	 */
	public function testForCompilerPostgreSQL(): void
	{
		$query = $this->getBuilder(Postgres::class);

		$query
		->forCompiler(Postgres::class, function ($query): void {
			$query->whereRaw('"created_at"::time = ?', ['14:00:00']);
		})
		->forCompiler(MySQL::class, function ($query): void {
			$query->whereRaw('TIME(`created_at`) = ?', ['14:00:00']);
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM "foobar" WHERE "created_at"::time = ?', $query['sql']);
		$this->assertEquals(['14:00:00'], $query['params']);
	}

	/**
	 *
	 */
	public function testForCompilerMySQL(): void
	{
		$query = $this->getBuilder(MySQL::class);

		$query
		->forCompiler(Postgres::class, function ($query): void {
			$query->whereRaw('"created_at"::time = ?', ['14:00:00']);
		})
		->forCompiler(MySQL::class, function ($query): void {
			$query->whereRaw('TIME(`created_at`) = ?', ['14:00:00']);
		});

		$query = $query->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE TIME(`created_at`) = ?', $query['sql']);
		$this->assertEquals(['14:00:00'], $query['params']);
	}
}
