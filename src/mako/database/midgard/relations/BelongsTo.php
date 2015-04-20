<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use mako\database\midgard\relations\Relation;

/**
 * Belongs to relation.
 *
 * @author  Frederic G. Østby
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

		foreach($results as $result)
		{
			$keys[] = $result->getRawColumn($this->getForeignKey());
		}

		return array_filter(array_unique($keys));
	}

	/**
	 * {@inheritdoc}
	 */

	protected function lazyCriterion()
	{
		$this->where($this->model->getPrimaryKey(), '=', $this->parent->getRawColumn($this->getForeignKey()));
	}

	/**
	 * Sets the criterion used when eager loading related records.
	 *
	 * @access  protected
	 * @param   array                                       $keys  Parent keys
	 * @return  \mako\database\midgard\relations\BelongsTo
	 */

	protected function eagerCriterion(array $keys)
	{
		$this->lazy = false;

		$this->in($this->model->getPrimaryKey(), $keys);

		return $this;
	}

	/**
	 * Eager loads related records and matches them with their parent records.
	 *
	 * @access  public
	 * @param   array          $results   Parent records
	 * @param   string         $relation  Relation name
	 * @param   null|\Closure  $criteria  Relation criteria
	 * @param   array          $includes  Includes passed from the parent record
	 */

	public function eagerLoad(array &$results, $relation, $criteria, array $includes)
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

		foreach($results as $result)
		{
			if(isset($grouped[$result->getRawColumn($this->getForeignKey())]))
			{
				$result->setRelated($relation, $grouped[$result->getRawColumn($this->getForeignKey())]);
			}
			else
			{
				$result->setRelated($relation, false);
			}
		}
	}

	/**
	 * Returns related a record from the database.
	 *
	 * @access  public
	 * @return  \mako\database\midgard\ORM|false
	 */

	public function getRelated()
	{
		if($this->parent->getRawColumn($this->getForeignKey()) === null)
		{
			return false;
		}

		return $this->first();
	}
}