<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query;

use JsonSerializable;

/**
 * Result.
 *
 * @author  Frederic G. Østby
 */
class Result implements JsonSerializable
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
	 * Returns data which can be serialized by json_encode().
	 *
	 * @access  public
	 * @return  array
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}

	/**
	 * Returns a json representation of the result.
	 *
	 * @access  public
	 * @param   int     $options  JSON encode options
	 * @return  string
	 */
	public function toJSON($options = 0)
	{
		return json_encode($this->toArray(), $options);
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