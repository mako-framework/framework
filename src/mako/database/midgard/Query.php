<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard;

use BadMethodCallException;
use Closure;
use PDO;

use mako\database\connections\Connection;
use mako\database\midgard\ORM;
use mako\database\midgard\ResultSet;
use mako\database\query\Query as QueryBuilder;

/**
 * ORM query builder.
 *
 * @author  Frederic G. Østby
 *
 * @method  \mako\database\midgard\ResultSet  paginate($itemsPerPage = null, array $options = [])
 */
class Query extends QueryBuilder
{
	/**
	 * Instance of the model to hydrate.
	 *
	 * @var \mako\database\midgard\ORM
	 */
	protected $model;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\database\connections\Connection  $connection  Database connection
	 * @param   \mako\database\midgard\ORM             $model       Model to hydrate
	 */
	public function __construct(Connection $connection, ORM $model)
	{
		parent::__construct($connection);

		$this->model = $model;

		$this->table = $model->getTable();
	}

	/**
	 * Returns the model.
	 *
	 * @access  public
	 * @return  \mako\database\midgard\ORM
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function join($table, $column1 = null, $operator = null, $column2 = null, $type = 'INNER', $raw = false)
	{
		if(empty($this->joins) && $this->columns === ['*'])
		{
			$this->select([$this->model->getTable() . '.*']);
		}

		return parent::join($table, $column1, $operator, $column2, $type, $raw);
	}

	/**
	 * {@inheritdoc}
	 */
	public function insert(array $values)
	{
		// Execute "beforeInsert" hooks

		foreach($this->model->getHooks('beforeInsert') as $hook)
		{
			$values = $hook($values, $this);
		}

		// Insert record

		$inserted = parent::insert($values);

		// Execute "afterInsert" hooks

		foreach($this->model->getHooks('afterInsert') as $hook)
		{
			$hook($inserted);
		}

		// Return insert status

		return $inserted;
	}

	/**
	 * {@inheritdoc}
	 */
	public function update(array $values)
	{
		// Execute "beforeUpdate" hooks

		foreach($this->model->getHooks('beforeUpdate') as $hook)
		{
			$values = $hook($values, $this);
		}

		// Update record(s)

		$updated = parent::update($values);

		// Execute "afterUpdate" hooks

		foreach($this->model->getHooks('afterUpdate') as $hook)
		{
			$hook($updated);
		}

		// Return number of affected rows

		return $updated;
	}

	/**
	 * {@inheritdoc}
	 */
	public function increment($column, $increment = 1)
	{
		if($this->model->exists())
		{
			$this->model->{$column} += $increment;

			$this->where($this->model->getPrimaryKey(), '=', $this->model->getPrimaryKeyValue());
		}

		$updated = parent::increment($column, $increment);

		if($this->model->exists())
		{
			$this->model->synchronize();
		}

		return $updated;
	}

	/**
	 * {@inheritdoc}
	 */
	public function decrement($column, $decrement = 1)
	{
		if($this->model->exists())
		{
			$this->model->{$column} -= $decrement;

			$this->where($this->model->getPrimaryKey(), '=', $this->model->getPrimaryKeyValue());
		}

		$updated = parent::decrement($column, $decrement);

		if($this->model->exists())
		{
			$this->model->synchronize();
		}

		return $updated;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete()
	{
		// Execute "beforeDelete" hooks

		foreach($this->model->getHooks('beforeDelete') as $hook)
		{
			$hook($this);
		}

		$deleted = parent::delete();

		// Execute "afterDelete" hooks

		foreach($this->model->getHooks('afterDelete') as $hook)
		{
			$hook($deleted);
		}

		return $deleted;
	}

	/**
	 * Returns a record using the value of its primary key.
	 *
	 * @access  public
	 * @param   int                         $id       Primary key
	 * @param   array                       $columns  Columns to select
	 * @return  \mako\database\midgard\ORM
	 */
	public function get($id, array $columns = [])
	{
		if(!empty($columns))
		{
			$this->select($columns);
		}

		return $this->where($this->model->getPrimaryKey(), '=', $id)->first();
	}

	/**
	 * Sets the relations to eager load.
	 *
	 * @access  public
	 * @param   string|array                  $includes  Relation or array of relations to eager load
	 * @return  \mako\database\midgard\Query
	 */
	public function including($includes)
	{
		$this->model->setIncludes((array) $includes);

		return $this;
	}

	/**
	 * Removes relations to eager load.
	 *
	 * @access  public
	 * @param   string|array                  $excludes  Relation or array of relations to exclude from eager loading
	 * @return  \mako\database\midgard\Query
	 */
	public function excluding($excludes)
	{
		$this->model->setIncludes(array_diff($this->model->getIncludes(), (array) $excludes));

		return $this;
	}

	/**
	 * Returns a hydrated model.
	 *
	 * @access  protected
	 * @param   object                      $result  Database result
	 * @return  \mako\database\midgard\ORM
	 */
	protected function hydrateModel($result)
	{
		$model = $this->model->getClass();

		return new $model($result, true, false, true);
	}

	/**
	 * Parses includes.
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function parseIncludes()
	{
		$includes = ['this' => [], 'forward' => []];

		foreach($this->model->getIncludes() as $include => $criteria)
		{
			if(is_numeric($include))
			{
				$include  = $criteria;
				$criteria = null;
			}

			if(($position = strpos($include, '.')) === false)
			{
				$includes['this'][$include] = $criteria;
			}
			else
			{
				if($criteria === null)
				{
					$includes['forward'][substr($include, 0, $position)][] = substr($include, $position + 1);
				}
				else
				{
					$includes['forward'][substr($include, 0, $position)][substr($include, $position + 1)] = $criteria;
				}
			}
		}

		return $includes;
	}


	/**
	 * Load includes.
	 *
	 * @access  protected
	 * @param   array      $results  Loaded records
	 */
	protected function loadIncludes(array $results)
	{
		$includes = $this->parseIncludes();

		foreach($includes['this'] as $include => $criteria)
		{
			$forward = $includes['forward'][$include] ?? [];

			$results[0]->{$include}()->eagerLoad($results, $include, $criteria, $forward);
		}
	}

	/**
	 * Returns hydrated models.
	 *
	 * @access  protected
	 * @param   mixed      $results  Database results
	 * @return  array
	 */
	protected function hydrateModelsAndLoadIncludes($results)
	{
		$hydrated = [];

		foreach($results as $result)
		{
			$hydrated[] = $this->hydrateModel($result);
		}

		if(!empty($hydrated))
		{
			$this->loadIncludes($hydrated);
		}

		return $hydrated;
	}

	/**
	 * Returns a single record from the database.
	 *
	 * @access  public
	 * @return  \mako\database\midgard\ORM
	 */
	public function first()
	{
		$result = $this->fetchFirst(PDO::FETCH_ASSOC);

		if($result !== false)
		{
			return $this->hydrateModelsAndLoadIncludes([$result])[0];
		}

		return false;
	}

	/**
	 * Returns a result set from the database.
	 *
	 * @access  public
	 * @return  \mako\database\midgard\ResultSet
	 */
	public function all()
	{
		$results = $this->fetchAll(false, PDO::FETCH_ASSOC);

		if(!empty($results))
		{
			$results = $this->hydrateModelsAndLoadIncludes($results);
		}

		return new ResultSet($results);
	}

	/**
	 * {@inheritdoc}
	 */
	public function batch(Closure $processor, $batchSize = 1000, $offsetStart = 0, $offsetEnd = null)
	{
		if(empty($this->orderings))
		{
			$this->ascending($this->model->getPrimaryKey());
		}

		parent::batch($processor, $batchSize, $offsetStart, $offsetEnd);
	}

	/**
	 * Magic method that allows us to call model scopes.
	 *
	 * @access  public
	 * @param   string                        $name       Method name
	 * @param   array                         $arguments  Method arguments
	 * @return  \mako\database\midgard\Query
	 */
	public function __call($name, $arguments)
	{
		if(!method_exists($this->model, $name . 'Scope'))
		{
			throw new BadMethodCallException(vsprintf("Call to undefined method %s::%s().", [__CLASS__, $name]));
		}

		return $this->model->{$name . 'Scope'}(...array_merge([$this], $arguments));
	}
}