<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\database;

use mako\database\ConnectionManager;
use mako\database\connections\Connection;
use mako\database\query\Query;
use mako\tests\TestCase;
use mako\validator\rules\database\Unique;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class UniqueTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		/** @var \mako\database\ConnectionManager|\Mockery\MockInterface $database */
		$database = Mockery::mock(ConnectionManager::class);

		$rule = new Unique('table', 'column', null, null, $database);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		/** @var \mako\database\query\Query|\Mockery\MockInterface $builder */
		$builder = Mockery::mock(Query::class);

		$builder->shouldReceive('table')->once()->with('users')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('email', '=', 'foo@example.org')->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(0);

		/** @var \mako\database\connections\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		/** @var \mako\database\ConnectionManager|\Mockery\MockInterface $database */
		$database = Mockery::mock(ConnectionManager::class);

		$database->shouldReceive('getConnection')->once()->with('foobar')->andReturn($connection);

		$rule = new Unique('users', 'email', null, 'foobar', $database);

		$this->assertTrue($rule->validate('foo@example.org', '', []));
	}

	/**
	 *
	 */
	public function testWithValidValueAndAllowedValue(): void
	{
		/** @var \mako\database\query\Query|\Mockery\MockInterface $builder */
		$builder = Mockery::mock(Query::class);

		$builder->shouldReceive('table')->once()->with('users')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('email', '=', 'foo@example.org')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('email', '!=', 'bar@example.org')->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(0);

		/** @var \mako\database\connections\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		/** @var \mako\database\ConnectionManager|\Mockery\MockInterface $database */
		$database = Mockery::mock(ConnectionManager::class);

		$database->shouldReceive('getConnection')->once()->with('foobar')->andReturn($connection);

		$rule = new Unique('users', 'email', 'bar@example.org', 'foobar', $database);

		$this->assertTrue($rule->validate('foo@example.org', '', []));
	}

	/**
	 *
	 */
	public function testWithSameValueAsTheAllowedValue(): void
	{
		/** @var \mako\database\ConnectionManager|\Mockery\MockInterface $database */
		$database = Mockery::mock(ConnectionManager::class);

		$rule = new Unique('users', 'email', 'foo@example.org', 'foobar', $database);

		$this->assertTrue($rule->validate('foo@example.org', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		/** @var \mako\database\query\Query|\Mockery\MockInterface $builder */
		$builder = Mockery::mock(Query::class);

		$builder->shouldReceive('table')->once()->with('users')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('email', '=', 'foo@example.org')->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(1);

		/** @var \mako\database\connections\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		/** @var \mako\database\ConnectionManager|\Mockery\MockInterface $database */
		$database = Mockery::mock(ConnectionManager::class);

		$database->shouldReceive('getConnection')->once()->with('foobar')->andReturn($connection);

		$rule = new Unique('users', 'email', null, 'foobar', $database);

		$this->assertFalse($rule->validate('foo@example.org', '', []));

		$this->assertSame('The foobar must be unique.', $rule->getErrorMessage('foobar'));
	}
}
