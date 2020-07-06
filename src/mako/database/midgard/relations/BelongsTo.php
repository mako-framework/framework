<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use Closure;

use function array_filter;
use function array_unique;

/**
 * Belongs to relation.
 *
 * @author Frederic G. Østby
 */
class BelongsTo extends Relation
{
	/**
	 * {@inheritdoc}
	 */
	protected function getForeignKey()
	{
		if($this->foreignKey === null)
		{
			$this->foreignKey = $this->model->getForeignKey();
		}

		return $this->foreignKey;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function keys(array $results)
	{
		$keys = [];

		$foreignKey = $this->getForeignKey();

		foreach($results as $result)
		{
			$keys[] = $result->getRawColumnValue($foreignKey);
		}

		return array_filter(array_unique($keys));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function lazyCriterion(): void
	{
		$this->where("{$this->table}.{$this->model->getPrimaryKey()}", '=', $this->origin->getRawColumnValue($this->getForeignKey()));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function eagerCriterion(array $keys)
	{
		$this->in("{$this->table}.{$this->model->getPrimaryKey()}", $keys);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getRelationCountQuery()
	{
		$this->whereColumn("{$this->table}.{$this->model->getPrimaryKey()}", '=', "{$this->origin->getTable()}.{$this->getForeignKey()}");

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

		if(!empty($keys = $this->keys($results)))
		{
			if($criteria !== null)
			{
				$criteria($this);
			}

			foreach($this->eagerLoadChunked($keys) as $related)
			{
				$grouped[$related->getPrimaryKeyValue()] = $related;
			}
		}

		$foreignKey = $this->getForeignKey();

		foreach($results as $result)
		{
			$result->setRelated($relation, $grouped[$result->getRawColumnValue($foreignKey)] ?? null);
		}
	}

	/**
	 * Returns related a record from the database.
	 *
	 * @return \mako\database\midgard\ORM|null
	 */
	public function getRelated()
	{
		if($this->origin->getRawColumnValue($this->getForeignKey()) === null)
		{
			return null;
		}

		return $this->first();
	}
}
