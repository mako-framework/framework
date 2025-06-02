<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

use JsonSerializable;
use mako\pagination\PaginationInterface;
use mako\utility\Arr;
use mako\utility\Collection;
use Stringable;

use function json_encode;

/**
 * Result set.
 *
 * @template TKey of array-key
 * @template TValue of object|Result
 * @extends Collection<TKey, TValue>
 */
class ResultSet extends Collection implements JsonSerializable, Stringable
{
	/**
	 * Pagination.
	 */
	protected ?PaginationInterface $pagination = null;

	/**
	 * Sets the pagination.
	 */
	public function setPagination(PaginationInterface $pagination): void
	{
		$this->pagination = $pagination;
	}

	/**
	 * Returns the pagination.
	 */
	public function getPagination(): ?PaginationInterface
	{
		return $this->pagination;
	}

	/**
	 * Returns an array containing only the values of chosen column.
	 */
	public function pluck(string $column): array
	{
		return Arr::pluck($this->items, $column);
	}

	/**
	 * Returns an array representation of the result set.
	 */
	public function toArray(): array
	{
		$results = [];

		foreach ($this->items as $item) {
			$results[] = $item->toArray();
		}

		return $results;
	}

	/**
	 * Returns data which can be serialized by json_encode().
	 *
	 * @return array|array{data: array, pagination: array}
	 */
	public function jsonSerialize(): array
	{
		if ($this->pagination === null) {
			return $this->toArray();
		}

		return ['data' => $this->toArray(), 'pagination' => $this->pagination->toArray()];
	}

	/**
	 * Returns a json representation of the result set.
	 */
	public function toJson(int $options = 0): string
	{
		return json_encode($this->jsonSerialize(), $options);
	}

	/**
	 * Returns a json representation of the result set.
	 */
	public function __toString(): string
	{
		return $this->toJson();
	}
}
