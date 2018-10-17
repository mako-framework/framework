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
	 * @param string|array|bool $column Column or relation to hide from the
	 */
	public function protect($column)
	{
		foreach($this->items as $item)
		{
			$item->protect($column);
		}
	}

	/**
	 * Exposes the chosen columns and relations in the array and json representations of the collection.
	 *
	 * @param string|array|bool $column Column or relation to hide from the
	 */
	public function expose($column)
	{
		foreach($this->items as $item)
		{
			$item->expose($column);
		}
	}

	/**
	 * Eager loads relations on the collection.
	 *
	 * @param  string|array                     $includes Relation or array of relations to eager load
	 * @return \mako\database\midgard\ResultSet
	 */
	public function include($includes): ResultSet
	{
		$items = $this->items;

		(function() use ($items)
		{
			$this->loadIncludes($items);
		})->bindTo($this->items[0]->builder()->including($includes), Query::class)();

		return $this;
	}
}
