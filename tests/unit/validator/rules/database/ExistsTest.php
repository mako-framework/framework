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
use mako\validator\rules\database\Exists;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ExistsTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$database = Mockery::mock(ConnectionManager::class);

		$rule = new Exists('table', 'column', null, $database);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$builder = Mockery::mock(Query::class);

		$builder->shouldReceive('table')->once()->with('users')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('email', '=', 'foo@example.org')->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(1);

		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$database = Mockery::mock(ConnectionManager::class);

		$database->shouldReceive('getConnection')->once()->with('foobar')->andReturn($connection);

		$rule = new Exists('users', 'email', 'foobar', $database);

		$this->assertTrue($rule->validate('foo@example.org', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$builder = Mockery::mock(Query::class);

		$builder->shouldReceive('table')->once()->with('users')->andReturn($builder);

		$builder->shouldReceive('where')->once()->with('email', '=', 'foo@example.org')->andReturn($builder);

		$builder->shouldReceive('count')->once()->andReturn(0);

		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('getQuery')->once()->andReturn($builder);

		$database = Mockery::mock(ConnectionManager::class);

		$database->shouldReceive('getConnection')->once()->with('foobar')->andReturn($connection);

		$rule = new Exists('users', 'email', 'foobar', $database);

		$this->assertFalse($rule->validate('foo@example.org', '', []));

		$this->assertSame('The foobar doesn\'t exist.', $rule->getErrorMessage('foobar'));
	}
}
