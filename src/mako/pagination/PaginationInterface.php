<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pagination;

use JsonSerializable;

/**
 * Pagination interface.
 */
interface PaginationInterface extends JsonSerializable
{
	/**
	 * Constructor.
	 */
	public function __construct(int $items, int $itemsPerPage, int $currentPage, array $options = []);

	/**
	 * Returns the number of items.
	 */
	public function items(): int;

	/**
	 * Returns the number of items per page.
	 */
	public function itemsPerPage(): int;

	/**
	 * Returns the current page.
	 */
	public function currentPage(): int;

	/**
	 * Returns the number pages.
	 */
	public function numberOfPages(): int;

	/**
	 * Returns TRUE if we're on a valid page and FALSE if not.
	 */
	public function isValidPage(): bool;

	/**
	 * Returns the limit.
	 */
	public function limit(): int;

	/**
	 * Returns the offset.
	 */
	public function offset(): int;

	/**
	 * Returns an array representation of the pagination object.
	 */
	public function toArray(): array;

	/**
	 * Returns a json representation of the pagination object.
	 */
	public function toJson(int $options = 0): string;
}
