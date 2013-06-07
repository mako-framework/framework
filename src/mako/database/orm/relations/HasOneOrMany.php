<?php

namespace mako\database\orm\relations;

/**
 * Has one or has many relation.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class HasOneOrMany extends \mako\database\orm\relations\Relation
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
	 * Creates a related record.
	 * 
	 * @access  public
	 * @param   mixed                $related  Related record
	 * @return  \mako\database\ORM
	 */

	public function create($related)
	{
		if($related instanceof $this->model)
		{
			$related->{$this->getForeignKey()} = $this->parent->getPrimaryKeyValue();

			$related->save();

			return $related;
		}
		else
		{
			return $this->model->create(array($this->getForeignKey() => $this->parent->getPrimaryKeyValue()) + $related);
		}
	}
}

/** -------------------- End of file -------------------- **/