<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

use AllowDynamicProperties;
use JsonSerializable;
use Override;
use Stringable;

use function get_object_vars;
use function json_encode;

/**
 * Result.
 */
#[AllowDynamicProperties]
class Result implements JsonSerializable, Stringable
{
	/**
	 * Returns an array representation of the result.
	 */
	public function toArray(): array
	{
		return get_object_vars($this);
	}

	/**
	 * Returns data which can be serialized by json_encode().
	 */
	#[Override]
	public function jsonSerialize(): mixed
	{
		return $this->toArray();
	}

	/**
	 * Returns a json representation of the result.
	 */
	public function toJson(int $options = 0): string
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * Returns a json representation of the result.
	 */
	#[Override]
	public function __toString(): string
	{
		return $this->toJson();
	}
}
