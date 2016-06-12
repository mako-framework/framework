<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\midgard;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\database\midgard\Query;

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
class QueryTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 *
	 */
	public function getConnecion()
	{
		$connection = Mockery::mock('\mako\database\connections\Connection');

		$connection->shouldReceive('getQueryBuilderHelper')->andReturn(Mockery::mock('\mako\database\query\helpers\HelperInterface'));

		$connection->shouldReceive('getQueryCompiler')->andReturn(Mockery::mock('\mako\database\query\compilers\Compiler'));

		return $connection;
	}

	/**
	 *
	 */
	public function getModel()
	{
		$model = Mockery::mock('\mako\database\midgard\ORM');

		return $model;
	}

	/**
	 *
	 */
	public function getQuery($model)
	{
		return Mockery::mock('\mako\database\midgard\Query', [$this->getConnecion(), $model]);
	}

	/**
	 *
	 */
	public function testConstructor()
	{
		$model = $this->getModel();

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$query = $this->getQuery($model);
	}

	/**
	 *
	 */
	public function testJoin()
	{
		$model = $this->getModel();

		$model->shouldReceive('getTable')->twice()->andReturn('tests');

		$query = new Query($this->getConnecion(), $model);

		$query->join('foos', 'tests.id', '=', 'foos.id');

		$this->assertEquals(['tests.*'], $query->getColumns());
	}

	/**
	 *
	 */
	public function testGet()
	{
		$model = $this->getModel();

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$model->shouldReceive('getPrimaryKey')->once()->andReturn('id');

		$query = Mockery::mock('\mako\database\midgard\Query[where,first]', [$this->getConnecion(), $model]);

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

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$model->shouldReceive('getPrimaryKey')->once()->andReturn('id');

		$query = Mockery::mock('\mako\database\midgard\Query[select,where,first]', [$this->getConnecion(), $model]);

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
		$model = Mockery::mock('\mako\database\midgard\ORM');

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$model->shouldReceive('getPrimaryKey')->andReturn('foobar');

		$query = Mockery::mock('\mako\database\midgard\Query[all]', [$this->getConnecion(), $model]);

		$query->shouldReceive('all')->once()->andReturn([]);

		$query->batch(function($results)
		{

		});

		$this->assertEquals([['column' => ['foobar'], 'order' => 'ASC']], $query->getOrderings());

		//

		$model = Mockery::mock('\mako\database\midgard\ORM');

		$model->shouldReceive('getTable')->once()->andReturn('tests');

		$query = Mockery::mock('\mako\database\midgard\Query[all]', [$this->getConnecion(), $model]);

		$query->shouldReceive('all')->once()->andReturn([]);

		$query->descending('barfoo')->batch(function($results)
		{

		});

		$this->assertEquals([['column' => ['barfoo'], 'order' => 'DESC']], $query->getOrderings());
	}
}