<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

/**
 * Has one or has many relation.
 *
 * @author Frederic G. Østby
 */
abstract class HasOneOrMany extends Relation
{
	/**
	 * Creates a related record.
	 *
	 * @param  mixed                      $related Related record
	 * @return \mako\database\midgard\ORM
	 */
	public function create($related)
	{
		if($related instanceof $this->model)
		{
			$related->{$this->getForeignKey()} = $this->parent->getPrimaryKeyValue();

			$related->save();

			return $related;
		}

		return $this->model->create([$this->getForeignKey() => $this->parent->getPrimaryKeyValue()] + $related);
	}
}
