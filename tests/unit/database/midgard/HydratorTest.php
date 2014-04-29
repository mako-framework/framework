<?php

use Mockery as m;

class HydratorTest extends PHPUnit_Framework_TestCase
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

	public function testGet()
	{
		$model = $this->getModel();

		$model->shouldReceive('isReadOnly')->once()->andReturn(false);

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$model->shouldReceive('getPrimaryKey')->once()->andReturn('id');

		$hydrator = m::mock('\mako\database\midgard\Hydrator[where,first]', [$this->getConnecion(), $model]);

		$hydrator->shouldReceive('where')->once()->with('id', '=', 1984)->andReturn($hydrator);

		$hydrator->shouldReceive('first')->once()->with([])->andReturn(true);

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

		$hydrator = m::mock('\mako\database\midgard\Hydrator[where,first]', [$this->getConnecion(), $model]);

		$hydrator->shouldReceive('where')->once()->with('id', '=', 1984)->andReturn($hydrator);

		$hydrator->shouldReceive('first')->once()->with(['foo', 'bar'])->andReturn(true);

		$this->assertTrue($hydrator->get(1984, ['foo', 'bar']));
	}
}