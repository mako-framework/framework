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
	 * Returns an array representation of the result set.
	 *
	 * @access  public
	 * @param   boolean  $protect  Protect columns?
	 * @param   boolean  $raw      Get raw values?
	 * @return  array
	 */
	public function toArray($protect = true, $raw = false)
	{
		$results = [];

		foreach($this->items as $item)
		{
			$results[] = $item->toArray($protect, $raw);
		}

		return $results;
	}

	/**
	 * Returns a json representation of the result set.
	 *
	 * @access  public
	 * @param   boolean  $protect  Protect columns?
	 * @param   boolean  $raw      Get raw values?
	 * @return  string
	 */
	public function toJson($protect = true, $raw = false)
	{
		return json_encode($this->toArray($protect, $raw));
	}
}