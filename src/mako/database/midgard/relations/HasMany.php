<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use \mako\database\midgard\ResultSet;

/**
 * Has many relation.
 *
 * @author  Frederic G. Østby
 */

class HasMany extends \mako\database\midgard\relations\HasOneOrMany
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Eager loads related records and matches them with their parent records.
	 * 
	 * @access  public
	 * @param   \mako\database\midgard\ResultSet  $results   Parent records
	 * @param   string                            $relation  Relation name
	 * @param   mixed                             $criteria  Relation criteria
	 * @param   array                             $includes  Includes passed from the parent record
	 */

	public function eagerLoad(&$results, $relation, $criteria, $includes)
	{
		$this->model->setIncludes($includes);

		$grouped = [];

		if($criteria !== null)
		{
			$criteria($this);
		}

		foreach($this->eagerCriterion($this->keys($results))->all() as $related)
		{
			$grouped[$related->getColumn($this->getForeignKey())][] = $related;
		}

		foreach($results as $result)
		{
			if(isset($grouped[$result->getPrimaryKeyValue()]))
			{
				$result->setRelated($relation, new ResultSet($grouped[$result->getPrimaryKeyValue()]));
			}
			else
			{
				$result->setRelated($relation, new ResultSet());
			}
		}
	}

	/**
	 * Returns a related result set from the database.
	 * 
	 * @access  public
	 * @return  \mako\database\midgard\ResultSet
	 */

	public function getRelated()
	{
		return $this->all();
	}
}