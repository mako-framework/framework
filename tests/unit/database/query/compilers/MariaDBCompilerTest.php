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
use mako\database\query\Subquery;
use mako\database\query\values\in\Vector as InVector;
use mako\database\query\values\out\Vector as OutVector;
use mako\database\query\values\out\VectorDistance as OutVectorDistance;
use mako\database\query\VectorDistance;
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
	public function testBasicCosineWhereVectorDistance(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->whereVectorDistance('embedding', [1, 2, 3, 4, 5], maxDistance: 0.5)
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE VEC_DISTANCE_COSINE(`embedding`, VEC_FromText(?)) <= ?', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]', 0.5], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicEuclidianWhereVectorDistance(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->whereVectorDistance('embedding', [1, 2, 3, 4, 5], maxDistance: 0.5, vectorDistance: VectorDistance::EUCLIDEAN)
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE VEC_DISTANCE_EUCLIDEAN(`embedding`, VEC_FromText(?)) <= ?', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]', 0.5], $query['params']);
	}

	/**
	 *
	 */
	public function testBasicCosineWhereVectorDistanceWithStringVector(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->whereVectorDistance('embedding', '[1,2,3,4,5]', maxDistance: 0.5)
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE VEC_DISTANCE_COSINE(`embedding`, VEC_FromText(?)) <= ?', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]', 0.5], $query['params']);
	}

	/**
	 *
	 */
	public function testCosineWhereVectorDistanceFromSubquery(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->whereVectorDistance('embedding', new Subquery(function (Query $query): void {
			$query->table('embeddings')->select(['embedding'])->where('id', '=', 1);
		}), maxDistance: 0.5)
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` WHERE VEC_DISTANCE_COSINE(`embedding`, (SELECT `embedding` FROM `embeddings` WHERE `id` = ?)) <= ?', $query['sql']);
		$this->assertEquals([1, 0.5], $query['params']);
	}

	/**
	 *
	 */
	public function testOrderByVectorDistanceCosine(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->orderByVectorDistance('embedding', [1, 2, 3, 4, 5])
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` ORDER BY VEC_DISTANCE_COSINE(`embedding`, VEC_FromText(?)) ASC', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]'], $query['params']);
	}

	/**
	 *
	 */
	public function testOrderByVectorDistanceCosineWithStringVector(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->orderByVectorDistance('embedding', '[1,2,3,4,5]')
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` ORDER BY VEC_DISTANCE_COSINE(`embedding`, VEC_FromText(?)) ASC', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]'], $query['params']);
	}

	/**
	 *
	 */
	public function testOrderByVectorDistanceCosineWithSubqueryVector(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->orderByVectorDistance('embedding', new Subquery(function (Query $query): void {
			$query->table('embeddings')->select(['embedding'])->where('id', '=', 1);
		}))
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` ORDER BY VEC_DISTANCE_COSINE(`embedding`, (SELECT `embedding` FROM `embeddings` WHERE `id` = ?)) ASC', $query['sql']);
		$this->assertEquals([1], $query['params']);
	}

	/**
	 *
	 */
	public function testOrderByVectorDistanceEuclidean(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->orderByVectorDistance('embedding', [1, 2, 3, 4, 5], VectorDistance::EUCLIDEAN)
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` ORDER BY VEC_DISTANCE_EUCLIDEAN(`embedding`, VEC_FromText(?)) ASC', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]'], $query['params']);
	}

	/**
	 *
	 */
	public function testAscendingVectorDistance(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->ascendingVectorDistance('embedding', [1, 2, 3, 4, 5])
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` ORDER BY VEC_DISTANCE_COSINE(`embedding`, VEC_FromText(?)) ASC', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]'], $query['params']);
	}

	/**
	 *
	 */
	public function testDescendingVectorDistance(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->descendingVectorDistance('embedding', [1, 2, 3, 4, 5])
		->getCompiler()->select();

		$this->assertEquals('SELECT * FROM `foobar` ORDER BY VEC_DISTANCE_COSINE(`embedding`, VEC_FromText(?)) DESC', $query['sql']);
		$this->assertEquals(['[1,2,3,4,5]'], $query['params']);
	}

	/**
	 *
	 */
	public function testVectorSelectValue(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->select([new OutVector('embedding')])
		->getCompiler()->select();

		$this->assertEquals('SELECT VEC_ToText(`embedding`) FROM `foobar`', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testVectorSelectValueWithAlias(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->select([new OutVector('embedding')->as('vector')])
		->getCompiler()->select();

		$this->assertEquals('SELECT VEC_ToText(`embedding`) AS `vector` FROM `foobar`', $query['sql']);
		$this->assertEquals([], $query['params']);
	}

	/**
	 *
	 */
	public function testVectorCosineDistanceSelectValue(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->select([new OutVectorDistance('embedding', [1, 2, 3, 4])])
		->getCompiler()->select();

		$this->assertEquals('SELECT VEC_DISTANCE_COSINE(`embedding`, VEC_FromText(?)) FROM `foobar`', $query['sql']);
		$this->assertEquals(['[1,2,3,4]'], $query['params']);
	}

	/**
	 *
	 */
	public function testVectorEuclideanDistanceSelectValue(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->select([new OutVectorDistance('embedding', [1, 2, 3, 4], VectorDistance::EUCLIDEAN)])
		->getCompiler()->select();

		$this->assertEquals('SELECT VEC_DISTANCE_EUCLIDEAN(`embedding`, VEC_FromText(?)) FROM `foobar`', $query['sql']);
		$this->assertEquals(['[1,2,3,4]'], $query['params']);
	}

	/**
	 *
	 */
	public function testVectorDistanceSelectValueWithAlias(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->select([new OutVectorDistance('embedding', [1, 2, 3, 4])->as('distance')])
		->getCompiler()->select();

		$this->assertEquals('SELECT VEC_DISTANCE_COSINE(`embedding`, VEC_FromText(?)) AS `distance` FROM `foobar`', $query['sql']);
		$this->assertEquals(['[1,2,3,4]'], $query['params']);
	}

	/**
	 *
	 */
	public function testVectorInsertValue(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->getCompiler()->insert(['embedding' => new InVector([1, 2, 3])]);

		$this->assertEquals('INSERT INTO `foobar` (`embedding`) VALUES (VEC_FromText(?))', $query['sql']);
		$this->assertEquals(['[1,2,3]'], $query['params']);
	}

	/**
	 *
	 */
	public function testStringVectorInsertValue(): void
	{
		$query = $this->getBuilder();

		$query = $query->table('foobar')
		->getCompiler()->insert(['embedding' => new InVector('[1,2,3]')]);

		$this->assertEquals('INSERT INTO `foobar` (`embedding`) VALUES (VEC_FromText(?))', $query['sql']);
		$this->assertEquals(['[1,2,3]'], $query['params']);
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
