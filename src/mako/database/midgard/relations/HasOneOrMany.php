<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

/**
 * Has one or has many relation.
 *
 * @author  Frederic G. Østby
 */

abstract class HasOneOrMany extends \mako\database\midgard\relations\Relation
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
	 * @param   mixed                    $related  Related record
	 * @return  \mako\database\midgard
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
			return $this->model->create([$this->getForeignKey() => $this->parent->getPrimaryKeyValue()] + $related);
		}
	}
}

