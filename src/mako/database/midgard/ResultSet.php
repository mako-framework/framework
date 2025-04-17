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
 * @template TKey of array-key
 * @template TValue of ORM
 * @extends BaseResultSet<TKey, TValue>
 */
class ResultSet extends BaseResultSet
{
	/**
	 * Clones all items when cloning the collection.
	 */
	public function __clone()
	{
		foreach ($this->items as $key => $value) {
			$this->items[$key] = clone $value;
		}
	}

	/**
	 * Excludes the chosen columns and relations from array and json representations of the collection.
	 *
	 * @return $this
	 */
	public function protect(array|false|string $column): static
	{
		foreach ($this->items as $item) {
			$item->protect($column);
		}

		return $this;
	}

	/**
	 * Exposes the chosen columns and relations in the array and json representations of the collection.
	 *
	 * @return $this
	 */
	public function expose(array|string|true $column): static
	{
		foreach ($this->items as $item) {
			$item->expose($column);
		}

		return $this;
	}

	/**
	 * Eager loads relations on the collection.
	 *
	 * @return $this
	 */
	public function include(array|string $includes): static
	{
		if (!empty($this->items)) {
			(function ($includes, $items): void {
				$this->including($includes)->loadIncludes($items);
			})->bindTo($this->items[0]->getQuery(), Query::class)($includes, $this->items);
		}

		return $this;
	}
}
