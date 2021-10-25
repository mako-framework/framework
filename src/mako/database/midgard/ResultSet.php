<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard;

use mako\database\query\ResultSet as BaseResultSet;

/**
 * ORM result set.
 *
 * @author Frederic G. Østby
 */
class ResultSet extends BaseResultSet
{
	/**
	 * Clones all items when cloning the collection.
	 */
	public function __clone()
	{
		foreach($this->items as $key => $value)
		{
			$this->items[$key] = clone $value;
		}
	}

	/**
	 * Excludes the chosen columns and relations from array and json representations of the collection.
	 *
	 * @param array|bool|string $column Column or relation to hide from the
	 */
	public function protect($column): void
	{
		foreach($this->items as $item)
		{
			$item->protect($column);
		}
	}

	/**
	 * Exposes the chosen columns and relations in the array and json representations of the collection.
	 *
	 * @param array|bool|string $column Column or relation to hide from the
	 */
	public function expose($column): void
	{
		foreach($this->items as $item)
		{
			$item->expose($column);
		}
	}

	/**
	 * Eager loads relations on the collection.
	 *
	 * @param  array|string $includes Relation or array of relations to eager load
	 * @return $this
	 */
	public function include($includes)
	{
		(function($includes, $items): void
		{
			$this->including($includes)->loadIncludes($items);
		})->bindTo($this->items[0]->builder(), Query::class)($includes, $this->items);

		return $this;
	}
}
