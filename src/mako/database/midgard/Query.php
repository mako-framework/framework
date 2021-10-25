<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard;

use Closure;
use Generator;
use mako\database\connections\Connection;
use mako\database\query\Query as QueryBuilder;
use mako\utility\Str;
use PDO;

use function array_filter;
use function array_keys;
use function array_merge;
use function array_udiff;
use function array_unique;
use function in_array;
use function is_numeric;
use function is_string;
use function strpos;
use function substr;

/**
 * ORM query builder.
 *
 * @author Frederic G. Østby
 *
 * @method \mako\database\midgard\ResultSet paginate($itemsPerPage = null, array $options = [])
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
	 * Class name of the model we're hydrating.
	 *
	 * @var string
	 */
	protected $modelClass;

	/**
	 * Constructor.
	 *
	 * @param \mako\database\connections\Connection $connection Database connection
	 * @param \mako\database\midgard\ORM            $model      Model to hydrate
	 */
	public function __construct(Connection $connection, ORM $model)
	{
		parent::__construct($connection);

		$this->model = $model;

		$this->modelClass = $model->getClass();

		$this->table = $model->getTable();
	}

	/**
	 * Returns the model.
	 *
	 * @return \mako\database\midgard\ORM
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * {@inheritDoc}
	 */
	public function join($table, $column1 = null, $operator = null, $column2 = null, $type = 'INNER', $raw = false)
	{
		if(empty($this->joins) && $this->columns === ['*'])
		{
			$this->select(["{$this->table}.*"]);
		}

		return parent::join($table, $column1, $operator, $column2, $type, $raw);
	}

	/**
	 * {@inheritDoc}
	 */
	public function insert(array $values = []): bool
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
	 * {@inheritDoc}
	 */
	public function update(array $values): int
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
	 * {@inheritDoc}
	 */
	public function increment($column, int $increment = 1): int
	{
		if($this->model->isPersisted())
		{
			$this->model->$column += $increment;

			$this->where($this->model->getPrimaryKey(), '=', $this->model->getPrimaryKeyValue());
		}

		$updated = parent::increment($column, $increment);

		if($this->model->isPersisted())
		{
			$this->model->synchronize();
		}

		return $updated;
	}

	/**
	 * {@inheritDoc}
	 */
	public function decrement($column, int $decrement = 1): int
	{
		if($this->model->isPersisted())
		{
			$this->model->$column -= $decrement;

			$this->where($this->model->getPrimaryKey(), '=', $this->model->getPrimaryKeyValue());
		}

		$updated = parent::decrement($column, $decrement);

		if($this->model->isPersisted())
		{
			$this->model->synchronize();
		}

		return $updated;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(): int
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
	 * @param  int|string                 $id      Primary key
	 * @param  array                      $columns Columns to select
	 * @return \mako\database\midgard\ORM
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
	 * Adds relations to eager load.
	 *
	 * @param  array|bool|string $includes Relation or array of relations to eager load
	 * @return $this
	 */
	public function including($includes)
	{
		if($includes === false)
		{
			$this->model->setIncludes([]);
		}
		else
		{
			$includes = (array) $includes;

			$currentIncludes = $this->model->getIncludes();

			if(!empty($currentIncludes))
			{
				$withCriterion = array_filter(array_keys($includes), 'is_string');

				if(!empty($withCriterion))
				{
					foreach($currentIncludes as $key => $value)
					{
						if(in_array($value, $withCriterion))
						{
							unset($currentIncludes[$key]); // Unset relations that have previously been set without a criterion closure
						}
					}
				}

				$includes = array_merge($currentIncludes, $includes);
			}

			$this->model->setIncludes(array_unique($includes, SORT_REGULAR));
		}

		return $this;
	}

	/**
	 * Removes relations to eager load.
	 *
	 * @param  array|bool|string $excludes Relation or array of relations to exclude from eager loading
	 * @return $this
	 */
	public function excluding($excludes)
	{
		if($excludes === true)
		{
			$this->model->setIncludes([]);
		}
		else
		{
			$excludes = (array) $excludes;

			$includes = $this->model->getIncludes();

			foreach($excludes as $key => $relation)
			{
				if(is_string($relation) && isset($includes[$relation]))
				{
					unset($includes[$relation], $excludes[$key]); // Unset relations that may have been set with a criterion closure
				}
			}

			$this->model->setIncludes(array_udiff($includes, $excludes, function($a, $b)
			{
				return $a === $b ? 0 : -1;
			}));
		}

		return $this;
	}

	/**
	 * Returns a hydrated model.
	 *
	 * @param  array                      $result Database result
	 * @return \mako\database\midgard\ORM
	 */
	protected function hydrateModel(array $result)
	{
		$model = $this->modelClass;

		return new $model($result, true, false, true);
	}

	/**
	 * Parses includes.
	 *
	 * @return array
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
	 * @param array $results Loaded records
	 */
	protected function loadIncludes(array $results): void
	{
		$includes = $this->parseIncludes();

		foreach($includes['this'] as $include => $criteria)
		{
			$forward = $includes['forward'][$include] ?? [];

			$results[0]->$include()->eagerLoad($results, $include, $criteria, $forward);
		}
	}

	/**
	 * Returns hydrated models.
	 *
	 * @param  mixed $results Database results
	 * @return array
	 */
	protected function hydrateModelsAndLoadIncludes($results)
	{
		$hydrated = [];

		foreach($results as $result)
		{
			$hydrated[] = $this->hydrateModel($result);
		}

		$this->loadIncludes($hydrated);

		return $hydrated;
	}

	/**
	 * Returns a single record from the database.
	 *
	 * @return false|\mako\database\midgard\ORM
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
	 * Creates a result set.
	 *
	 * @param  array                            $results Results
	 * @return \mako\database\midgard\ResultSet
	 */
	protected function createResultSet(array $results)
	{
		return new ResultSet($results);
	}

	/**
	 * Returns a result set from the database.
	 *
	 * @return \mako\database\midgard\ResultSet
	 */
	public function all()
	{
		$results = $this->fetchAll(false, PDO::FETCH_ASSOC);

		if(!empty($results))
		{
			$results = $this->hydrateModelsAndLoadIncludes($results);
		}

		return $this->createResultSet($results);
	}

	/**
	 * Returns a generator that lets you iterate over the results.
	 *
	 * @return \Generator
	 */
	public function yield(): Generator
	{
		foreach($this->fetchYield(PDO::FETCH_ASSOC) as $row)
		{
			yield $this->hydrateModel($row);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function batch(Closure $processor, $batchSize = 1000, $offsetStart = 0, $offsetEnd = null): void
	{
		if(empty($this->orderings))
		{
			$this->ascending($this->model->getPrimaryKey());
		}

		parent::batch($processor, $batchSize, $offsetStart, $offsetEnd);
	}

	/**
	 * Calls a scope method on the model.
	 *
	 * @param  string $scope        Scope
	 * @param  mixed  ...$arguments Arguments
	 * @return $this
	 */
	public function scope(string $scope, ...$arguments)
	{
		$this->model->{Str::underscored2camel($scope) . 'Scope'}(...array_merge([$this], $arguments));

		return $this;
	}
}
