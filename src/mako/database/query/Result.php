<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query;

/**
 * Result.
 *
 * @author  Frederic G. Østby
 */
class Result
{
	/**
	 * Returns an array representation of the result.
	 *
	 * @access  public
	 * @return  array
	 */
	public function toArray()
	{
		return get_object_vars($this);
	}

	/**
	 * Returns a json representation of the result.
	 *
	 * @access  public
	 * @return  string
	 */
	public function toJSON()
	{
		return json_encode(get_object_vars($this));
	}

	/**
	 * Returns a json representation of the result.
	 *
	 * @access  public
	 * @return  string
	 */
	public function __toString()
	{
		return $this->toJSON();
	}
}