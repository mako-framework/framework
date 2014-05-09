<?php

namespace mako\tests\unit\database\midgard;

use \mako\database\midgard\Hydrator;

use \Mockery as m;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class ScopedModel extends \mako\database\midgard\ORM
{
	protected $tableName = 'foos';

	public function popularScope($query)
	{
		return $query->from('barfoo');
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class HydratorTest extends \PHPUnit_Framework_TestCase
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

		$connection->shouldReceive('getCompiler')->andReturn('sqlite');

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

	public function getHydrator($model)
	{
		return m::mock('\mako\database\midgard\Hydrator', [$this->getConnecion(), $model]);
	}

	/**
	 * 
	 */

	public function testConstructor()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$hydrator = $this->getHydrator($model);
	}

	/**
	 * 
	 */

	public function testJoin()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->twice()->andReturn('tests');

		$hydrator = new Hydrator($this->getConnecion(), $model);

		$hydrator->join('foos', 'tests.id', '=', 'foos.id');

		$this->assertEquals(['tests.*'], $hydrator->getColumns());
	}

	/**
	 * @expectedException \mako\database\midgard\ReadOnlyRecordException
	 */

	public function testInsertWithException()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->twice()->andReturn(true);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$hydrator = new Hydrator($this->getConnecion(), $model);

		$hydrator->insert(['foo' => 'bar']);
	}

	/**
	 * @expectedException \mako\database\midgard\ReadOnlyRecordException
	 */

	public function testUpdateWithException()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->twice()->andReturn(true);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$hydrator = new Hydrator($this->getConnecion(), $model);

		$hydrator->update(['foo' => 'bar']);
	}

	/**
	 * @expectedException \mako\database\midgard\ReadOnlyRecordException
	 */

	public function testDeleteWithException()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->twice()->andReturn(true);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$hydrator = new Hydrator($this->getConnecion(), $model);

		$hydrator->delete();
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

		$hydrator = m::mock('\mako\database\midgard\Hydrator[where,first]', [$this->getConnecion(), $model]);

		$hydrator->shouldReceive('where')->once()->with('id', '=', 1984)->andReturn($hydrator);

		$hydrator->shouldReceive('first')->once()->andReturn(true);

		$this->assertTrue($hydrator->get(1984));
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

		$hydrator = m::mock('\mako\database\midgard\Hydrator[select,where,first]', [$this->getConnecion(), $model]);

		$hydrator->shouldReceive('select')->once()->with(['foo', 'bar']);

		$hydrator->shouldReceive('where')->once()->with('id', '=', 1984)->andReturn($hydrator);

		$hydrator->shouldReceive('first')->once()->andReturn(true);

		$this->assertTrue($hydrator->get(1984, ['foo', 'bar']));
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

		$hydrator = new Hydrator($this->getConnecion(), $model);

		$hydrator->including('foo');
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

		$hydrator = new Hydrator($this->getConnecion(), $model);

		$hydrator->including(['foo', 'bar']);
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

		$hydrator = new Hydrator($this->getConnecion(), $model);

		$hydrator->excluding('bar');
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

		$hydrator = new Hydrator($this->getConnecion(), $model);

		$hydrator->excluding(['foo', 'bar']);
	}

	/**
	 * @expectedException \BadMethodCallException
	 */

	public function testScopeException()
	{
		$model = new ScopedModel();

		$hydrator = new Hydrator($this->getConnecion(), $model);

		$hydrator->unpopular();
	}

	/**
	 * 
	 */

	public function testScope()
	{
		$model = new ScopedModel();

		$hydrator = new Hydrator($this->getConnecion(), $model);

		$hydrator->popular();

		$this->assertEquals($hydrator->getTable(), 'barfoo');
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

		$hydrator = m::mock('\mako\database\midgard\Hydrator[all]', [$this->getConnecion(), $model]);

		$hydrator->shouldReceive('all')->once()->andReturn([]);

		$hydrator->batch(function($results)
		{

		});

		$this->assertEquals([['column' => ['foobar'], 'order' => 'ASC']], $hydrator->getOrderings());

		//

		$model = m::mock('\mako\database\midgard\ORM');

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$hydrator = m::mock('\mako\database\midgard\Hydrator[all]', [$this->getConnecion(), $model]);

		$hydrator->shouldReceive('all')->once()->andReturn([]);

		$hydrator->descending('barfoo')->batch(function($results)
		{

		});

		$this->assertEquals([['column' => ['barfoo'], 'order' => 'DESC']], $hydrator->getOrderings());
	}
}