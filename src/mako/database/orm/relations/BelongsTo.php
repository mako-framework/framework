<?php

namespace mako\database\orm\relations;

/**
 * Belongs to relation.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class BelongsTo extends \mako\database\orm\relations\Relation
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
	 * Returns the foreign key.
	 * 
	 * @access  protected
	 * @return  string
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
	 * Returns the keys used to eagerly load records.
	 * 
	 * @access  protected
	 * @param   \mako\database\orm\ResultSet  $results  Result set
	 * @return  array
	 */

	protected function keys($results)
	{
		$keys = array();
		
		foreach($results as $result)
		{
			$keys[] = $result->getColumn($this->getForeignKey());
		}

		return array_unique($keys);
	}

	/**
	 * Sets the criterion used when lazy loading related records.
	 * 
	 * @access  protected
	 */

	protected function lazyCriterion()
	{
		$this->where($this->model->getPrimaryKey(), '=', $this->parent->getColumn($this->getForeignKey()));
	}

	/**
	 * Sets the criterion used when eager loading related records.
	 * 
	 * @access  protected
	 * @param   array                                   $keys  Parent keys
	 * @return  \mako\database\orm\relations\BelongsTo
	 */

	protected function eagerCriterion($keys)
	{
		$this->lazy = false;

		$this->in($this->model->getPrimaryKey(), $keys);

		return $this;
	}

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
			$grouped[$related->getPrimaryKeyValue()] = $related;
		}

		foreach($results as $result)
		{
			if(isset($grouped[$result->getColumn($this->getForeignKey())]))
			{
				$result->setRelated($relation, $grouped[$result->getColumn($this->getForeignKey())]);
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