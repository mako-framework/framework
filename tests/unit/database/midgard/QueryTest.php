<?php

namespace mako\tests\unit\database\midgard;

use mako\database\midgard\Query;

use \Mockery as m;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class ScopedModel extends \mako\database\midgard\ORM
{
	protected $tableName = 'foos';

	public function popularScope($query)
	{
		return $query->table('barfoo');
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class QueryTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function getConnecion()
	{
		$connection = m::mock('\mako\database\Connection');

		$connection->shouldReceive('getDialect')->andReturn('sqlite');

		return $connection;
	}

	/**
	 *
	 */

	public function getModel()
	{
		$model = m::mock('\mako\database\midgard\ORM');

		return $model;
	}

	/**
	 *
	 */

	public function getQuery($model)
	{
		return m::mock('\mako\database\midgard\Query', [$this->getConnecion(), $model]);
	}

	/**
	 *
	 */

	public function testConstructor()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$query = $this->getQuery($model);
	}

	/**
	 *
	 */

	public function testJoin()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->twice()->andReturn('tests');

		$query = new Query($this->getConnecion(), $model);

		$query->join('foos', 'tests.id', '=', 'foos.id');

		$this->assertEquals(['tests.*'], $query->getColumns());
	}

	/**
	 * @expectedException \mako\database\midgard\ReadOnlyRecordException
	 */

	public function testInsertWithException()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->twice()->andReturn(true);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$query = new Query($this->getConnecion(), $model);

		$query->insert(['foo' => 'bar']);
	}

	/**
	 * @expectedException \mako\database\midgard\ReadOnlyRecordException
	 */

	public function testUpdateWithException()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->twice()->andReturn(true);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$query = new Query($this->getConnecion(), $model);

		$query->update(['foo' => 'bar']);
	}

	/**
	 * @expectedException \mako\database\midgard\ReadOnlyRecordException
	 */

	public function testDeleteWithException()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->twice()->andReturn(true);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$query = new Query($this->getConnecion(), $model);

		$query->delete();
	}

	/**
	 *
	 */

	public function testGet()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$model->shouldReceive('getPrimaryKey')->once()->andReturn('id');

		$query = m::mock('\mako\database\midgard\Query[where,first]', [$this->getConnecion(), $model]);

		$query->shouldReceive('where')->once()->with('id', '=', 1984)->andReturn($query);

		$query->shouldReceive('first')->once()->andReturn(true);

		$this->assertTrue($query->get(1984));
	}

	/**
	 *
	 */

	public function testGetWithColumns()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$model->shouldReceive('getPrimaryKey')->once()->andReturn('id');

		$query = m::mock('\mako\database\midgard\Query[select,where,first]', [$this->getConnecion(), $model]);

		$query->shouldReceive('select')->once()->with(['foo', 'bar']);

		$query->shouldReceive('where')->once()->with('id', '=', 1984)->andReturn($query);

		$query->shouldReceive('first')->once()->andReturn(true);

		$this->assertTrue($query->get(1984, ['foo', 'bar']));
	}

	/**
	 *
	 */

	public function testSingleInclude()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$model->shouldReceive('setIncludes')->once()->with(['foo'])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->including('foo');
	}

	/**
	 *
	 */

	public function testIncludes()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$model->shouldReceive('setIncludes')->once()->with(['foo', 'bar'])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->including(['foo', 'bar']);
	}

	/**
	 *
	 */

	public function testSingleExlude()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$model->shouldReceive('getIncludes')->once()->andReturn(['foo', 'bar']);

		$model->shouldReceive('setIncludes')->once()->with(['foo'])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->excluding('bar');
	}

	/**
	 *
	 */

	public function testExcludes()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$model->shouldReceive('getIncludes')->once()->andReturn(['foo', 'bar']);

		$model->shouldReceive('setIncludes')->once()->with([])->andReturn($model);

		$query = new Query($this->getConnecion(), $model);

		$query->excluding(['foo', 'bar']);
	}

	/**
	 * @expectedException \BadMethodCallException
	 */

	public function testScopeException()
	{
		$model = new ScopedModel();

		$query = new Query($this->getConnecion(), $model);

		$query->unpopular();
	}

	/**
	 *
	 */

	public function testScope()
	{
		$model = new ScopedModel();

		$query = new Query($this->getConnecion(), $model);

		$query->popular();

		$this->assertEquals($query->getTable(), 'barfoo');
	}

	/**
	 *
	 */

	public function testBatch()
	{
		$model = m::mock('\mako\database\midgard\ORM');

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$model->shouldReceive('getPrimaryKey')->andReturn('foobar');

		$query = m::mock('\mako\database\midgard\Query[all]', [$this->getConnecion(), $model]);

		$query->shouldReceive('all')->once()->andReturn([]);

		$query->batch(function($results)
		{

		});

		$this->assertEquals([['column' => ['foobar'], 'order' => 'ASC']], $query->getOrderings());

		//

		$model = m::mock('\mako\database\midgard\ORM');

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$query = m::mock('\mako\database\midgard\Query[all]', [$this->getConnecion(), $model]);

		$query->shouldReceive('all')->once()->andReturn([]);

		$query->descending('barfoo')->batch(function($results)
		{

		});

		$this->assertEquals([['column' => ['barfoo'], 'order' => 'DESC']], $query->getOrderings());
	}
}