<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard;

use mako\utility\Arr;
use mako\database\query\ResultSet as BaseResultSet;

/**
 * ORM result set.
 *
 * @author  Frederic G. Østby
 */
class ResultSet extends BaseResultSet
{
	/**
	 * Clones all items when cloning the collection.
	 *
	 * @access  public
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
	 * @access  public
	 * @param   string|array|bool  $column  Column or relation to hide from the
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
	 * @access  public
	 * @param   string|array|bool  $column  Column or relation to hide from the
	 */
	public function expose($column)
	{
		foreach($this->items as $item)
		{
			$item->expose($column);
		}
	}
}
