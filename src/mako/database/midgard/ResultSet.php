<?php

namespace mako\database\midgard;

use \mako\utility\Arr;
use \mako\utility\Collection;

/**
 * ORM result set.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class ResultSet extends \mako\utility\Collection
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

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

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns an array containing only the values of chosen column.
	 * 
	 * @access  public
	 * @param   string  $column  Column name
	 * @return  array
	 */

	public function pluck($column)
	{
		return Arr::pluck($this->items, $column);
	}

	/**
	 * Returns an array representation of the result set.
	 * 
	 * @access  public
	 * @param   boolean  $protect  (optional) Protect columns?
	 * @param   boolean  $raw      (optional) Get raw values?
	 * @return  array
	 */

	public function toArray($protect = true, $raw = false)
	{
		$results = array();

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
	 * @param   boolean  $protect  (optional) Protect columns?
	 * @param   boolean  $raw      (optional) Get raw values?
	 * @return  string
	 */

	public function toJson($protect = true, $raw = false)
	{
		return json_encode($this->toArray($protect, $raw));
	}

	/**
	 * Returns a json representation of the result set.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function __toString()
	{
		return $this->toJson();
	}
}

/** -------------------- End of file -------------------- **/