<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use mako\database\midgard\ORM;

/**
 * Has one or has many relation.
 */
abstract class HasOneOrMany extends Relation
{
	/**
	 * Creates a related record.
	 */
	public function create(array|ORM $related): ORM
	{
		if($related instanceof $this->model)
		{
			$related->{$this->getForeignKey()} = $this->origin->getPrimaryKeyValue();

			$related->save();

			return $related;
		}

		return $this->model->create([$this->getForeignKey() => $this->origin->getPrimaryKeyValue()] + $related);
	}
}
