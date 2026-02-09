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
use mako\database\query\VectorMetric;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class MariaDBCompilerTest extends TestCase
{
	/**
	 *
	 */
	protected function getConnection(): MariaDBConnection&MockInterface
	{
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
	public function testBasicCosineWhereVectorSimilarity(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->whereVectorSimilarity('embedding', [1, 2, 3, 4, 5], minSimilarity: 0.75)
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE EXP(-VEC_DISTANCE_COSINE(`embedding`, VEC_FromText(?))) >= ?', $query['sql']);
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

		$this->assertEquals('SELECT * FROM `foobar` WHERE EXP(-VEC_DISTANCE_EUCLIDEAN(`embedding`, VEC_FromText(?))) >= ?', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]', 0.75], $query['params']);
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

	/**
	 *
	 */
	public function testInsertMultipleAndReturn(): void
	{
		$query = $this->getBuilder();

		$query = $query->getCompiler()->insertMultipleAndReturn(['id', 'foo'], ['foo' => 'bar'], ['bar' => 'baz']);

		$this->assertEquals('INSERT INTO `foobar` (`foo`) VALUES (?), (?) RETURNING `id`, `foo`', $query['sql']);
		$this->assertEquals(['bar', 'baz'], $query['params']);
	}
}
