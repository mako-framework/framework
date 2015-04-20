<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use mako\database\midgard\relations\HasOneOrMany;

/**
 * Has one relation.
 *
 * @author  Frederic G. Østby
 */

class HasOne extends HasOneOrMany
{
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

		if($criteria !== null)
		{
			$criteria($this);
		}

		foreach($this->eagerLoadChunked($this->keys($results)) as $related)
		{
			$grouped[$related->getRawColumn($this->getForeignKey())] = $related;
		}

		foreach($results as $result)
		{
			if(isset($grouped[$result->getPrimaryKeyValue()]))
			{
				$result->setRelated($relation, $grouped[$result->getPrimaryKeyValue()]);
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
		return $this->first();
	}
}