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
	 *
	 * @param  int                                  $items        Number of items
	 * @param  int|null                             $itemsPerPage Number of items per page
	 * @param  array                                $options      Pagination options
	 * @return \mako\pagination\PaginationInterface
	 */
	public function create(int $items, ?int $itemsPerPage = null, array $options = []): PaginationInterface;
}
