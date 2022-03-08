<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\midgard;

use mako\database\connections\Connection;
use mako\database\midgard\ORM;
use mako\database\midgard\Query;
use mako\database\midgard\ResultSet;
use mako\database\query\compilers\Compiler;
use mako\database\query\helpers\HelperInterface;
use mako\tests\TestCase;
use Mockery;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class ScopedModel extends ORM
{
	protected $tableName = 'foos';

	public function popularScope($query): void
	{
		$query->table('barfoo');
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class QueryTest extends TestCase
{
	/**
	 * @return \mako\database\connections\Connection|\Mockery\MockInterface
	 */
	public function getConnecion()
	{
		/** @var \mako\database\connections\Connection|\Mockery\MockInterface $connection */
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock(HelperInterface::class));

		$connection->shouldReceive('getQueryCompiler')->andReturn(Mockery::mock(Compiler::class));

		return $connection;
	}

	/**
	 * @return \mako\database\midgard\ORM|\Mockery\MockInterface
	 */
	public function getModel()
	{
		/** @var \mako\database\midgard\ORM|\Mockery\MockInterface $model */
		$model = Mockery::mock(ORM::class);

		$model->shouldReceive('getClass')->once()->andReturn('Test');

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		return $model;
	}

	/**
	 * @return \mako\database\midgard\Query|\Mockery\MockInterface
	 */
	public function getQuery($model)
	{
		return Mockery::mock(Query::class, [$this->getConnecion(), $model]);
	}

	/**
	 *
	 */
	public function testConstructor(): void
	{
		$model = $this->getModel();

		$this->getQuery($model);
	}

	/**
	 *
	 */
	public function testJoin(): void
	{
		$model = $this->getModel();

		$query = new Query($this->getConnecion(), $model);

		$query->join('foos', 'tests.id', '=', 'foos.id');

		$this->assertEquals(['tests.*'], $query->getColumns());
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$model = $this->getModel();

		$model->shouldReceive('getPrimaryKey')->once()->andReturn('id');

		/** @var \mako\database\midgard\Query|\Mockery\MockInterface $query */
		$query = Mockery::mock(Query::class . '[where,first]', [$this->getConnecion(), $model]);

		$query->shouldReceive('where')->once()->with('id', '=', 1984)->andReturn($query);

		$query->shouldReceive('first')->once()->andReturn(true);

		$this->assertTrue($query->get(1984));
	}

	/**
	 *
	 */
	public function testGetWithColumns(): void
	{
		$model = $this->getModel();

		$model->shouldReceive('getPrimaryKey')->once()->andReturn('id');

		/** @var \mako\database\midgard\Query|\Mockery\MockInterface $query */
		$query = Mockery::mock(Query::class . '[select,where,first]', [$this->getConnecion(), $model]);

		$query->shouldReceive('select')->once()->with(['foo', 'bar']);

		$query->shouldReceive('where')->once()->with('id', '=', 1984)->andReturn($query);

		$query->shouldReceive('first')->once()->andReturn(true);

		$this->assertTrue($query->get(1984, ['foo', 'bar']));
	}

	/**
	 *
	 */
	public function testSingleInclude(): void
	{
		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn([]);

		$model->shouldReceive('setIncludes')->once()->with(['foo'])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->including('foo');

		//

		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn(['bar']);

		$model->shouldReceive('setIncludes')->once()->with(['bar', 'foo'])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->including('foo');

		//

		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn(['foo']);

		$model->shouldReceive('setIncludes')->once()->with(['foo'])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->including('foo');
	}

	/**
	 *
	 */
	public function testIncludes(): void
	{
		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn([]);

		$model->shouldReceive('setIncludes')->once()->with(['foo', 'bar'])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->including(['foo', 'bar']);
	}

	/**
	 *
	 */
	public function testIncludesWithCriterion(): void
	{
		$closure1 = function(): void {};

		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn([]);

		$model->shouldReceive('setIncludes')->once()->with(['foo', 'bar' => $closure1])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->including(['foo', 'bar' => $closure1]);

		//

		$closure1 = function(): void {};

		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn(['bar']);

		$model->shouldReceive('setIncludes')->once()->with(['foo', 'bar' => $closure1])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->including(['foo', 'bar' => $closure1]);

		//

		$closure1 = function(): void {};
		$closure2 = function(): void {};

		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn(['bar' => $closure1]);

		$model->shouldReceive('setIncludes')->once()->with(['foo', 'bar' => $closure2])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->including(['foo', 'bar' => $closure2]);
	}

	/**
	 *
	 */
	public function testIncludeNone(): void
	{
		$model = $this->getModel();

		$model->shouldReceive('setIncludes')->once()->with([])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->including(false);
	}

	/**
	 *
	 */
	public function testSingleExlude(): void
	{
		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn(['foo', 'bar']);

		$model->shouldReceive('setIncludes')->once()->with(['foo'])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->excluding('bar');
	}

	/**
	 *
	 */
	public function testExcludes(): void
	{
		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn(['foo', 'bar']);

		$model->shouldReceive('setIncludes')->once()->with([])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->excluding(['foo', 'bar']);

		//

		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn(['foo', 'bar', 'baz']);

		$model->shouldReceive('setIncludes')->once()->with([1 => 'bar'])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->excluding(['foo', 'baz']);
	}

	/**
	 *
	 */
	public function testExludeWithCriterion(): void
	{
		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn(['foo', 'bar' => function(): void {}]);

		$model->shouldReceive('setIncludes')->once()->with([])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->excluding(['foo', 'bar']);

		//

		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn(['foo', 'bar' => function(): void {}, 'baz']);

		$model->shouldReceive('setIncludes')->once()->with([1 => 'baz'])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->excluding(['foo', 'bar']);

		//

		$closure1 = function(): void {};
		$closure2 = function(): void {};

		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn(['foo', 'bar' => $closure1, 'baz' => $closure2]);

		$model->shouldReceive('setIncludes')->once()->with(['baz' => $closure2])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->excluding(['foo', 'bar']);

		//

		$closure1 = function(): void {};
		$closure2 = function(): void {};

		$model = $this->getModel();

		$model->shouldReceive('getIncludes')->once()->andReturn(['foo', 'bar' => $closure1, 'baz' => $closure2]);

		$model->shouldReceive('setIncludes')->once()->with(['baz' => $closure2])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->excluding(['foo', $closure1]);
	}

	/**
	 *
	 */
	public function testExcludeAll(): void
	{
		$model = $this->getModel();

		$model->shouldReceive('setIncludes')->once()->with([])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->excluding(true);
	}

	/**
	 *
	 */
	public function testScope(): void
	{
		$model = new ScopedModel();

		$query = new Query($this->getConnecion(), $model);

		$query->scope('popular');

		$this->assertEquals($query->getTable(), 'barfoo');
	}

	/**
	 *
	 */
	public function testBatch(): void
	{
		$model = $this->getModel();

		$model->shouldReceive('getPrimaryKey')->andReturn('foobar');

		/** @var \mako\database\midgard\Query|\Mockery\MockInterface $query */
		$query = Mockery::mock(Query::class . '[all]', [$this->getConnecion(), $model]);

		$query->shouldReceive('all')->once()->andReturn(new ResultSet());

		$query->batch(function($results): void
		{

		});

		$this->assertEquals([['column' => ['foobar'], 'order' => 'ASC']], $query->getOrderings());

		//

		$model = $this->getModel();

		/** @var \mako\database\midgard\Query|\Mockery\MockInterface $query */
		$query = Mockery::mock(Query::class . '[all]', [$this->getConnecion(), $model]);

		$query->shouldReceive('all')->once()->andReturn(new ResultSet());

		$query->descending('barfoo')->batch(function($results): void
		{

		});

		$this->assertEquals([['column' => ['barfoo'], 'order' => 'DESC']], $query->getOrderings());
	}
}
