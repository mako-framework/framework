<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pagination;

/**
 * Pagination factory interface.
 */
interface PaginationFactoryInterface
{
	/**
	 * Creates and returns a pagination instance.
	 */
	public function create(int $items, ?int $itemsPerPage = null, array $options = []): PaginationInterface;
}
