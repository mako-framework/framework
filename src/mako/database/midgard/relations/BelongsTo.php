<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

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
	protected function lazyCriterion()
	{
		$this->where($this->table . '.' . $this->model->getPrimaryKey(), '=', $this->parent->getRawColumnValue($this->getForeignKey()));
	}

	/**
	 * Sets the criterion used when eager loading related records.
	 *
	 * @param  array                                      $keys Parent keys
	 * @return \mako\database\midgard\relations\BelongsTo
	 */
	protected function eagerCriterion(array $keys)
	{
		$this->lazy = false;

		$this->in($this->table . '.' . $this->model->getPrimaryKey(), $keys);

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
	public function eagerLoad(array &$results, string $relation, $criteria, array $includes)
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
			$result->setRelated($relation, $grouped[$result->getRawColumnValue($foreignKey)] ?? false);
		}
	}

	/**
	 * Returns related a record from the database.
	 *
	 * @return \mako\database\midgard\ORM|false
	 */
	public function getRelated()
	{
		if($this->parent->getRawColumnValue($this->getForeignKey()) === null)
		{
			return false;
		}

		return $this->first();
	}
}
