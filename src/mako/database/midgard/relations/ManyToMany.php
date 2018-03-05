<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use mako\database\connections\Connection;
use mako\database\midgard\ORM;

/**
 * Many to many relation.
 *
 * @author Frederic G. Østby
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
	 * @param \mako\database\midgard\ORM            $parent        Parent model
	 * @param \mako\database\midgard\ORM            $related       Related model
	 * @param string|null                           $foreignKey    Foreign key name
	 * @param string|null                           $junctionTable Junction table name
	 * @param string|null                           $junctionKey   Junction key name
	 */
	public function __construct(Connection $connection, ORM $parent, ORM $related, $foreignKey = null, $junctionTable = null, $junctionKey = null)
	{
		$this->junctionTable = $junctionTable;

		$this->junctionKey = $junctionKey;

		parent::__construct($connection, $parent, $related, $foreignKey);

		$this->junctionJoin();

		$this->columns = [$this->model->getTable() . '.*'];
	}

	/**
	 * Columns to include with the result.
	 * @param  array                                       $columns Columns
	 * @return \mako\database\midgard\relations\ManyToMany
	 */
	public function alongWith(array $columns)
	{
		foreach($columns as $key => $value)
		{
			if(strpos($value, '.') === false)
			{
				$columns[$key] = $this->getJunctionTable() . '.' . $value;
			}
		}

		$this->alongWith = $columns;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getColumns(): array
	{
		return array_merge($this->columns, $this->alongWith);
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
	 * Returns the the junction table.
	 *
	 * @return string
	 */
	protected function getJunctionTable()
	{
		if($this->junctionTable === null)
		{
			$tables = [$this->parent->getTable(), $this->model->getTable()];

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
	protected function junctionJoin()
	{
		$this->join($this->getJunctionTable(), $this->getJunctionTable() . '.' . $this->getJunctionKey(), '=', $this->model->getTable() . '.' . $this->model->getPrimaryKey());
	}

	/**
	 * {@inheritdoc}
	 */
	protected function lazyCriterion()
	{
		$this->where($this->getJunctionTable() . '.' . $this->getForeignKey(), '=', $this->parent->getPrimaryKeyValue());
	}

	/**
	 * Sets the criterion used when eager loading related records.
	 *
	 * @param  array                                    $keys Parent keys
	 * @return \mako\database\midgard\relations\HasMany
	 */
	protected function eagerCriterion(array $keys)
	{
		$this->lazy = false;

		$this->in($this->getJunctionTable() . '.' . $this->getForeignKey(), $keys);

		return $this;
	}

	/**
	 * Eager loads related records and matches them with their parent records.
	 *
	 * @param array         &$results Parent records
	 * @param string        $relation Relation name
	 * @param \Closure|null $criteria Relation criteria
	 * @param array         $includes Includes passed from the parent record
	 */
	public function eagerLoad(array &$results, $relation, $criteria, array $includes)
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

			unset($related->{$foreignKey}); // Unset as its not a part of the record
		}

		foreach($results as $result)
		{
			$result->setRelated($relation, $this->createResultSet($grouped[$result->getPrimaryKeyValue()] ?? []));
		}
	}

	/**
	 * Adjusts the query.
	 */
	protected function adjustQuery()
	{
		if(!$this->lazy)
		{
			$this->columns = array_merge($this->columns, [$this->getJunctionTable() . '.' . $this->getForeignKey()]);
		}

		parent::adjustQuery();
	}

	/**
	 * Returns a related result set from the database.
	 *
	 * @return \mako\database\midgard\ORM
	 */
	public function getRelated()
	{
		return $this->all();
	}

	/**
	 * Returns a query builder instance to the junction table.
	 *
	 * @return \mako\database\query\Query
	 */
	protected function junction()
	{
		return $this->connection->builder()->table($this->getJunctionTable());
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

		$foreignKeyValue = $this->parent->getPrimaryKeyValue();

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

		$foreignKeyValue = $this->parent->getPrimaryKeyValue();

		$junctionKey = $this->getJunctionKey();

		foreach($this->getJunctionKeys($id) as $key => $id)
		{
			$success = $success && (bool) $this->junction()->where($foreignKey, '=', $foreignKeyValue)->where($junctionKey, '=', $id)->update($this->getJunctionAttributes($key, $attributes));
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
		$query = $this->junction()->where($this->getForeignKey(), '=', $this->parent->getPrimaryKeyValue());

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

		$existing = $this->junction()->where($this->getForeignKey(), '=', $this->parent->getPrimaryKeyValue())->select([$this->getJunctionKey()])->all()->pluck($this->getJunctionKey());

		// Link new relations

		if(!empty($diff = array_diff($keys, $existing)))
		{
			$success = $success && $this->link($diff);
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
