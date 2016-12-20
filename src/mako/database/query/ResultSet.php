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

/**
 * Result set.
 *
 * @author Frederic G. Østby
 */
class ResultSet extends Collection implements JsonSerializable
{
	/**
	 * Pagination.
	 *
	 * @var \mako\pagination\PaginationInterface
	 */
	protected $pagination;

	/**
	 * Sets the pagination.
	 *
	 * @access public
	 * @param \mako\pagination\PaginationInterface $pagination Pagination
	 */
	public function setPagination(PaginationInterface $pagination)
	{
		$this->pagination = $pagination;
	}

	/**
	 * Returns the pagination.
	 *
	 * @access public
	 * @return \mako\pagination\PaginationInterface
	 */
	public function getPagination(): PaginationInterface
	{
		return $this->pagination;
	}

	/**
	 * Returns an array containing only the values of chosen column.
	 *
	 * @access public
	 * @param  string $column Column name
	 * @return array
	 */
	public function pluck(string $column): array
	{
		return Arr::pluck($this->items, $column);
	}

	/**
	 * Returns an array representation of the result set.
	 *
	 * @access public
	 * @return array
	 */
	public function toArray(): array
	{
		$results = [];

		foreach($this->items as $item)
		{
			$results[] = $item->toArray();
		}

		return $results;
	}

	/**
	 * Returns data which can be serialized by json_encode().
	 *
	 * @access public
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * Returns a json representation of the result set.
	 *
	 * @access public
	 * @param  int    $options JSON encode options
	 * @return string
	 */
	public function toJson(int $options = 0): string
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * Returns a json representation of the result set.
	 *
	 * @access public
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->toJson();
	}
}
