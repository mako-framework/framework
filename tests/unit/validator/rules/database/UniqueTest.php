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

/**
 * @group unit
 */
class UniqueTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Unique(Mockery::mock(ConnectionManager::class));

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$builder = Mockery::mock(Query::class);

		$builder->shouldReceive('where')->once()->with('email', '=', 'foo@example.org')->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(0);

		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('table')->once()->with('users')->andReturn($builder);

		$database = Mockery::mock(ConnectionManager::class);

		$database->shouldReceive('connection')->once()->with('foobar')->andReturn($connection);

		$rule = new Unique($database);

		$rule->setParameters(['users', 'email', null, 'foobar']);

		$this->assertTrue($rule->validate('foo@example.org', []));
	}

	/**
	 *
	 */
	public function testWithValidValueAndAllowedValue(): void
	{
		$builder = Mockery::mock(Query::class);

		$builder->shouldReceive('where')->once()->with('email', '=', 'foo@example.org')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('email', '!=', 'bar@example.org')->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(0);

		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('table')->once()->with('users')->andReturn($builder);

		$database = Mockery::mock(ConnectionManager::class);

		$database->shouldReceive('connection')->once()->with('foobar')->andReturn($connection);

		$rule = new Unique($database);

		$rule->setParameters(['users', 'email', 'bar@example.org', 'foobar']);

		$this->assertTrue($rule->validate('foo@example.org', []));
	}

	/**
	 *
	 */
	public function testWithSameValueAsTheAllowedValue(): void
	{
		$database = Mockery::mock(ConnectionManager::class);

		$rule = new Unique($database);

		$rule->setParameters(['users', 'email', 'foo@example.org', 'foobar']);

		$this->assertTrue($rule->validate('foo@example.org', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$builder = Mockery::mock(Query::class);

		$builder->shouldReceive('where')->once()->with('email', '=', 'foo@example.org')->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(1);

		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('table')->once()->with('users')->andReturn($builder);

		$database = Mockery::mock(ConnectionManager::class);

		$database->shouldReceive('connection')->once()->with('foobar')->andReturn($connection);

		$rule = new Unique($database);

		$rule->setParameters(['users', 'email', null, 'foobar']);

		$this->assertFalse($rule->validate('foo@example.org', []));

		$this->assertSame('The foobar must be unique.', $rule->getErrorMessage('foobar'));
	}
}
