<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

/**
 * Has one or has many relation.
 */
abstract class HasOneOrMany extends Relation
{
	/**
	 * Creates a related record.
	 *
	 * @param  \mako\database\midgard\ORM|array $related Related record
	 * @return \mako\database\midgard\ORM
	 */
	public function create($related)
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
