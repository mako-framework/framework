<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard;

use Closure;
use Generator;
use mako\database\connections\Connection;
use mako\database\exceptions\NotFoundException;
use mako\database\query\Query as QueryBuilder;
use mako\database\query\Raw;
use mako\database\query\Subquery;
use mako\utility\Str;
use PDO;

use function array_filter;
use function array_keys;
use function array_merge;
use function array_udiff;
use function array_unique;
use function explode;
use function in_array;
use function is_int;
use function is_string;
use function stripos;
use function strpos;
use function substr;

/**
 * ORM query builder.
 *
 * @method \mako\database\midgard\ResultSet paginate($itemsPerPage = null, array $options = [])
 */
class Query extends QueryBuilder
{
	/**
	 * Class name of the model we're hydrating.
	 *
	 * @var string
	 */
	protected $modelClass;

	/**
	 * Relation count subqueries.
	 *
	 * @var array
	 */
	protected $relationCounters = [];

	/**
	 * Constructor.
	 *
	 * @param \mako\database\connections\Connection $connection Database connection
	 * @param \mako\database\midgard\ORM            $model      Model to hydrate
	 */
	public function __construct(
		Connection $connection,
		protected ORM $model
	)
	{
		parent::__construct($connection);

		$this->modelClass = $model->getClass();

		$this->table = $model->getTable();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getColumns(): array
	{
		if(empty($this->relationCounters))
		{
			return $this->columns;
		}

		return [...$this->columns, ...$this->relationCounters];
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
	public function join(Raw|Subquery|string $table, Closure|Raw|string|null $column1 = null, ?string $operator = null, Raw|string|null $column2 = null, string $type = 'INNER', bool $lateral = false)
	{
		if(empty($this->joins) && $this->columns === ['*'])
		{
			$this->select(["{$this->table}.*"]);
		}

		return parent::join($table, $column1, $operator, $column2, $type, $lateral);
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
	public function increment(string $column, int $increment = 1): int
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
	public function decrement(string $column, int $decrement = 1): int
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
	 * @param  int|string                      $id      Primary key
	 * @param  array                           $columns Columns to select
	 * @return \mako\database\midgard\ORM|null
	 */
	public function get(int|string $id, array $columns = [])
	{
		if(!empty($columns))
		{
			$this->select($columns);
		}

		return $this->where($this->model->getPrimaryKey(), '=', $id)->first();
	}

	/**
	 * Returns a record using the value of its primary key.
	 *
	 * @param  int|string                 $id        Primary key
	 * @param  array                      $columns   Columns to select
	 * @param  string                     $exception Exception class
	 * @return \mako\database\midgard\ORM
	 */
	public function getOrThrow(int|string $id, array $columns = [], string $exception = NotFoundException::class)
	{
		if(!empty($columns))
		{
			$this->select($columns);
		}

		return $this->where($this->model->getPrimaryKey(), '=', $id)->firstOrThrow($exception);
	}

	/**
	 * Adds relations to eager load.
	 *
	 * @param  array|false|string $includes Relation or array of relations to eager load
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
	 * @param  array|string|true $excludes Relation or array of relations to exclude from eager loading
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

			$this->model->setIncludes(array_udiff($includes, $excludes, static fn ($a, $b) => $a === $b ? 0 : -1));
		}

		return $this;
	}

	/**
	 * Parses the count relation name and returns an array consisting of the relation name and the chosen alias.
	 *
	 * @param  string $relation Relation name
	 * @return array
	 */
	protected function parseRelationCountName(string $relation): array
	{
		if(stripos($relation, ' AS ') === false)
		{
			return [$relation, "{$relation}_count"];
		}

		[$relation, , $alias] = explode(' ', $relation, 3);

		return [$relation, $alias];
	}

	/**
	 * Adds subqueries that count the number of related records for the chosen relations.
	 *
	 * @param  array|string $relations Relation or array of relations to count
	 * @return $this
	 */
	public function withCountOf(array|string $relations)
	{
		foreach((array) $relations as $relation => $criteria)
		{
			if(is_int($relation))
			{
				$relation = $criteria;
				$criteria = null;
			}

			[$relation, $alias] = $this->parseRelationCountName($relation);

			/** @var \mako\database\midgard\relations\Relation $countQuery */
			$countQuery = $this->model->$relation()->getRelationCountQuery()->inSubqueryContext();

			if($criteria !== null)
			{
				$criteria($countQuery);
			}

			$this->relationCounters[] = new Subquery(static function (&$query) use ($countQuery): void
			{
				$query = $countQuery;

				$query->clearOrderings()->count();
			}, $alias, true);
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
		return new ($this->modelClass)($result, true, false, true);
	}

	/**
	 * Parses includes.
	 *
	 * @return array
	 */
	protected function parseIncludes(): array
	{
		$includes = ['this' => [], 'forward' => []];

		foreach($this->model->getIncludes() as $include => $criteria)
		{
			if(is_int($include))
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
	 * Parses include names.
	 *
	 * @param  string $name Include name
	 * @return array
	 */
	protected function parseIncludeName(string $name): array
	{
		if(stripos($name, ' AS ') === false)
		{
			return [$name, $name];
		}

		[$name, , $alias] = explode(' ', $name, 3);

		return [$name, $alias];
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
			[$methodName, $propertyName] = $this->parseIncludeName($include);

			$forward = $includes['forward'][$methodName] ?? [];

			$results[0]->$methodName()->eagerLoad($results, $propertyName, $criteria, $forward);
		}
	}

	/**
	 * Returns hydrated models.
	 *
	 * @param  mixed $results Database results
	 * @return array
	 */
	protected function hydrateModelsAndLoadIncludes(mixed $results): array
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
	 * @return \mako\database\midgard\ORM|null
	 */
	public function first(): mixed
	{
		$result = $this->fetchFirst(PDO::FETCH_ASSOC);

		if($result !== null)
		{
			return $this->hydrateModelsAndLoadIncludes([$result])[0];
		}

		return null;
	}

	/**
	 * Returns a single record from the database.
	 *
	 * @param  string                     $exception Exception class
	 * @return \mako\database\midgard\ORM
	 */
	public function firstOrThrow(string $exception = NotFoundException::class): mixed
	{
		return $this->hydrateModelsAndLoadIncludes([$this->fetchFirstOrThrow($exception, PDO::FETCH_ASSOC)])[0];
	}

	/**
	 * Creates a result set.
	 *
	 * @param  array                            $results Results
	 * @return \mako\database\midgard\ResultSet
	 */
	protected function createResultSet(array $results): ResultSet
	{
		return new ResultSet($results);
	}

	/**
	 * Returns a result set from the database.
	 *
	 * @return \mako\database\midgard\ResultSet
	 */
	public function all(): ResultSet
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
		/** @var array $row */
		foreach($this->fetchYield(PDO::FETCH_ASSOC) as $row)
		{
			yield $this->hydrateModel($row);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function batch(Closure $processor, int $batchSize = 1000, int $offsetStart = 0, ?int $offsetEnd = null): void
	{
		if(empty($this->orderings))
		{
			$this->ascending($this->model->getPrimaryKey());
		}

		parent::batch($processor, $batchSize, $offsetStart, $offsetEnd);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function aggregate(string $function, array|Raw|string $column)
	{
		// Empty "relationCounters" when performing aggregate queries

		$this->relationCounters = [];

		// Execute parent and return results

		return parent::aggregate($function, $column);
	}

	/**
	 * Calls a scope method on the model.
	 *
	 * @param  string $scope        Scope
	 * @param  mixed  ...$arguments Arguments
	 * @return $this
	 */
	public function scope(string $scope, mixed ...$arguments)
	{
		$this->model->{Str::snakeToCamel($scope) . 'Scope'}(...[$this, ...$arguments]);

		return $this;
	}
}
