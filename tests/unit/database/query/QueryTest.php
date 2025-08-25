<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query;

use mako\database\connections\Connection;
use mako\database\query\compilers\Compiler;
use mako\database\query\helpers\HelperInterface;
use mako\database\query\Query;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class QueryTest extends TestCase
{
	/**
	 *
	 */
	public function getQuery(): Query
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock(HelperInterface::class));

		$connection->shouldReceive('getQueryCompiler')->andReturn(Mockery::mock(Compiler::class));

		return new Query($connection);
	}

	/**
	 *
	 */
	public function testFrom(): void
	{
		$query = $this->getQuery();

		$query->from('foobar');

		$this->assertSame('foobar', $query->getTable());
	}

	/**
	 *
	 */
	public function testInto(): void
	{
		$query = $this->getQuery();

		$query->into('foobar');

		$this->assertSame('foobar', $query->getTable());
	}
}
