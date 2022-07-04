<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

use JsonSerializable;

use function get_object_vars;
use function json_encode;

/**
 * Result.
 */
#[\AllowDynamicProperties]
class Result implements JsonSerializable
{
	/**
	 * Returns an array representation of the result.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return get_object_vars($this);
	}

	/**
	 * Returns data which can be serialized by json_encode().
	 *
	 * @return mixed
	 */
	public function jsonSerialize(): mixed
	{
		return $this->toArray();
	}

	/**
	 * Returns a json representation of the result.
	 *
	 * @param  int    $options JSON encode options
	 * @return string
	 */
	public function toJson(int $options = 0): string
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * Returns a json representation of the result.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->toJson();
	}
}
