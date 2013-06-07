<?php

namespace mako\database\orm\relations;

/**
 * Has one relation.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class HasOne extends \mako\database\orm\relations\HasOneOrMany
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
	 * @param   \mako\database\orm\ResultSet  $results   Parent records
	 * @param   string                        $relation  Relation name
	 * @param   mixed                         $criteria  Relation criteria 
	 * @param   array                         $includes  Includes passed from the parent record
	 */

	public function eagerLoad(&$results, $relation, $criteria, $includes)
	{
		$this->model->setIncludes($includes);

		$grouped = array();

		if($criteria !== null)
		{
			$criteria($this);
		}

		foreach($this->eagerCriterion($this->keys($results))->all() as $related)
		{
			$grouped[$related->getColumn($this->getForeignKey())] = $related;
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
	 * Returns a record from the database.
	 * 
	 * @access  public
	 * @return  \mako\database\orm\ResultSet
	 */

	public function get()
	{
		return $this->first();
	}
}

/** -------------------- End of file -------------------- **/