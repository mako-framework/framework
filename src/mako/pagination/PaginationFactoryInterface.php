<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pagination;

use mako\pagination\PaginationInterface;

/**
 * Pagination factory interface.
 *
 * @author  Frederic G. Østby
 * @author  Yamada Taro
 */

interface PaginationFactoryInterface
{
	/**
	 * Creates and returns a pagination instance.
	 *
	 * @access  public
	 * @param   int                                   $items         Number of items
	 * @param   null|int                              $itemsPerPage  Number of items per page
	 * @param   array                                 $options       Pagination options
	 * @return  \mako\pagination\PaginationInterface
	 */

	public function create($items, $itemsPerPage = null, array $options = []): PaginationInterface;
}