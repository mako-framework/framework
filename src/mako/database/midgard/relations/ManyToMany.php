<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use Closure;
use mako\database\connections\Connection;
use mako\database\midgard\ORM;

use function array_diff;
use function array_merge;
use function array_shift;
use function count;
use function implode;
use function is_array;
use function sort;
use function strpos;

/**
 * Many to many relation.
 */
class ManyToMany extends Relation
{
	/**
	 * Junction table.
	 *
	 * @var string
	 */
	protected $junctionTable = null;

	/**
	 * Junction key.
	 *
	 * @var string
	 */
	protected $junctionKey = null;

	/**
	 * Junction columns to include in the result.
	 *
	 * @var array
	 */
	protected $alongWith = [];

	/**
	 * Constructor.
	 *
	 * @param \mako\database\connections\Connection $connection    Database connection
	 * @param \mako\database\midgard\ORM            $origin        Originating model
	 * @param \mako\database\midgard\ORM            $model         Related model
	 * @param string|null                           $foreignKey    Foreign key name
	 * @param string|null                           $junctionTable Junction table name
	 * @param string|null                           $junctionKey   Junction key name
	 */
	public function __construct(Connection $connection, ORM $origin, ORM $model, ?string $foreignKey = null, ?string $junctionTable = null, ?string $junctionKey = null)
	{
		$this->junctionTable = $junctionTable;

		$this->junctionKey = $junctionKey;

		parent::__construct($connection, $origin, $model, $foreignKey);

		$this->junctionJoin();

		$this->columns = ["{$this->model->getTable()}.*"];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getColumns(): array
	{
		if($this->lazy)
		{
			return array_merge(parent::getColumns(), $this->alongWith);
		}

		return array_merge(parent::getColumns(), $this->alongWith, ["{$this->getJunctionTable()}.{$this->getForeignKey()}"]);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function aggregate($function, $column)
	{
		// Empty "alongWith" when performing aggregate queries

		$this->alongWith = [];

		// Execute parent and return results

		return parent::aggregate($function, $column);
	}

	/**
	 * Columns to include with the result.
	 *
	 * @param  array $columns Columns
	 * @return $this
	 */
	public function alongWith(array $columns)
	{
		foreach($columns as $key => $value)
		{
			if(strpos($value, '.') === false)
			{
				$columns[$key] = "{$this->getJunctionTable()}.{$value}";
			}
		}

		$this->alongWith = $columns;

		return $this;
	}

	/**
	 * Returns the the junction table.
	 *
	 * @return string
	 */
	protected function getJunctionTable()
	{
		if($this->junctionTable === null)
		{
			$tables = [$this->origin->getTable(), $this->model->getTable()];

			sort($tables);

			$this->junctionTable = implode('_', $tables);
		}

		return $this->junctionTable;
	}

	/**
	 * Returns the the junction key.
	 *
	 * @return string
	 */
	protected function getJunctionKey()
	{
		if($this->junctionKey === null)
		{
			$this->junctionKey = $this->model->getForeignKey();
		}

		return $this->junctionKey;
	}

	/**
	 * Joins the junction table.
	 */
	protected function junctionJoin(): void
	{
		$this->join($this->getJunctionTable(), "{$this->getJunctionTable()}.{$this->getJunctionKey()}", '=', "{$this->model->getTable()}.{$this->model->getPrimaryKey()}");
	}

	/**
	 * {@inheritDoc}
	 */
	protected function lazyCriterion(): void
	{
		$this->where("{$this->getJunctionTable()}.{$this->getForeignKey()}", '=', $this->origin->getPrimaryKeyValue());
	}

	/**
	 * {@inheritDoc}
	 */
	protected function eagerCriterion(array $keys)
	{
		$this->in("{$this->getJunctionTable()}.{$this->getForeignKey()}", $keys);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getRelationCountQuery()
	{
		$this->whereColumn("{$this->getJunctionTable()}.{$this->getForeignKey()}", '=', "{$this->origin->getTable()}.{$this->origin->getPrimaryKey()}");

		return $this;
	}

	/**
	 * Eager loads related records and matches them with their originating records.
	 *
	 * @param array         &$results Originating records
	 * @param string        $relation Relation name
	 * @param \Closure|null $criteria Relation criteria
	 * @param array         $includes Includes passed from the originating record
	 */
	public function eagerLoad(array &$results, string $relation, ?Closure $criteria, array $includes): void
	{
		$this->model->setIncludes($includes);

		$grouped = [];

		if($criteria !== null)
		{
			$criteria($this);
		}

		$foreignKey = $this->getForeignKey();

		foreach($this->eagerLoadChunked($this->keys($results)) as $related)
		{
			$grouped[$related->getRawColumnValue($foreignKey)][] = $related;

			unset($related->$foreignKey); // Unset as it's not a part of the record
		}

		foreach($results as $result)
		{
			$result->setRelated($relation, $this->createResultSet($grouped[$result->getPrimaryKeyValue()] ?? []));
		}
	}

	/**
	 * Fetches a related result set from the database.
	 *
	 * @return \mako\database\midgard\ResultSet
	 */
	protected function fetchRelated()
	{
		return $this->all();
	}

	/**
	 * Returns a query builder instance to the junction table.
	 *
	 * @param  bool                       $includeWheres Should the wheres be included?
	 * @return \mako\database\query\Query
	 */
	protected function junction(bool $includeWheres = false)
	{
		$query = $this->connection->builder()->table($this->getJunctionTable());

		if($includeWheres && count($this->wheres) > 1)
		{
			$query->wheres = $this->wheres;

			array_shift($query->wheres);
		}

		return $query;
	}

	/**
	 * Returns an array of ids.
	 *
	 * @param  mixed $id Id, model or an array of ids and/or models
	 * @return array
	 */
	protected function getJunctionKeys($id): array
	{
		$ids = [];

		foreach((is_array($id) ? $id : [$id]) as $value)
		{
			if($value instanceof $this->model)
			{
				$value = $value->getPrimaryKeyValue();
			}

			$ids[] = $value;
		}

		return $ids;
	}

	/**
	 * Get junction attributes.
	 *
	 * @param  mixed $key        Key
	 * @param  array $attributes Attributes
	 * @return array
	 */
	protected function getJunctionAttributes($key, array $attributes): array
	{
		if(isset($attributes[$key]))
		{
			return $attributes[$key];
		}

		return $attributes;
	}

	/**
	 * Links related records.
	 *
	 * @param  mixed $id         Id, model or an array of ids and/or models
	 * @param  array $attributes Junction attributes
	 * @return bool
	 */
	public function link($id, array $attributes = []): bool
	{
		$success = true;

		$foreignKey = $this->getForeignKey();

		$foreignKeyValue = $this->origin->getPrimaryKeyValue();

		$junctionKey = $this->getJunctionKey();

		foreach($this->getJunctionKeys($id) as $key => $id)
		{
			$columns = [$foreignKey  => $foreignKeyValue, $junctionKey => $id];

			$success = $success && $this->junction()->insert($columns + $this->getJunctionAttributes($key, $attributes));
		}

		return $success;
	}

	/**
	 * Updates junction attributes.
	 *
	 * @param  mixed $id         Id, model or an array of ids and/or models
	 * @param  array $attributes Junction attributes
	 * @return bool
	 */
	public function updateLink($id, array $attributes): bool
	{
		$success = true;

		$foreignKey = $this->getForeignKey();

		$foreignKeyValue = $this->origin->getPrimaryKeyValue();

		$junctionKey = $this->getJunctionKey();

		foreach($this->getJunctionKeys($id) as $key => $id)
		{
			$success = $success && (bool) $this->junction(true)->where($foreignKey, '=', $foreignKeyValue)->where($junctionKey, '=', $id)->update($this->getJunctionAttributes($key, $attributes));
		}

		return $success;
	}

	/**
	 * Unlinks related records.
	 *
	 * @param  mixed $id Id, model or an array of ids and/or models
	 * @return bool
	 */
	public function unlink($id = null): bool
	{
		$query = $this->junction(true)->where($this->getForeignKey(), '=', $this->origin->getPrimaryKeyValue());

		if($id !== null)
		{
			$query->in($this->getJunctionKey(), $this->getJunctionKeys($id));
		}

		return (bool) $query->delete();
	}

	/**
	 * Synchronize related records.
	 *
	 * @param  array $ids An array of ids and/or models
	 * @return bool
	 */
	public function synchronize(array $ids): bool
	{
		$success = true;

		$keys = $this->getJunctionKeys($ids);

		// Fetch existing links

		$existing = $this->junction()->where($this->getForeignKey(), '=', $this->origin->getPrimaryKeyValue())->select([$this->getJunctionKey()])->all()->pluck($this->getJunctionKey());

		// Link new relations

		if(!empty($diff = array_diff($keys, $existing)))
		{
			$success = $this->link($diff);
		}

		// Unlink old relations

		if(!empty($diff = array_diff($existing, $keys)))
		{
			$success = $success && $this->unlink($diff);
		}

		// Return status

		return $success;
	}
}
