<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query\compilers;

use mako\database\connections\MariaDB as MariaDBConnection;
use mako\database\query\compilers\MariaDB as MariaDBCompiler;
use mako\database\query\helpers\HelperInterface;
use mako\database\query\Query;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class MariaDBCompilerTest extends TestCase
{
	/**
	 * @return MariaDBConnection|Mockery\MockInterface
	 */
	protected function getConnection()
	{
		/** @var MariaDBConnection|Mockery\MockInterface $connection */
		$connection = Mockery::mock(MariaDBConnection::class);

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock(HelperInterface::class));

		$connection->shouldReceive('getQueryCompiler')->andReturnUsing(function ($query) {
			return new MariaDBCompiler($query);
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
	public function testInsertAndReturn(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->insertAndReturn(['foo' => 'bar'], ['id', 'foo']);

		$this->assertEquals('INSERT INTO `foobar` (`foo`) VALUES (?) RETURNING `id`, `foo`', $query['sql']);
		$this->assertEquals(['bar'], $query['params']);
	}
}
