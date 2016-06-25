<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pagination;

/**
 * Pagination interface.
 *
 * @author  Frederic G. Østby
 * @author  Yamada Taro
 */
interface PaginationInterface
{
	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   int     $items         Number of items
	 * @param   int     $itemsPerPage  Number of items per page
	 * @param   int     $currentPage   The current page
	 * @param   array   $options       Pagination options
	 */
	public function __construct(int $items, int $itemsPerPage, int $currentPage, array $options = []);

	/**
	 * Returns the number of items.
	 *
	 * @access  public
	 * @return  int
	 */
	public function items(): int;

	/**
	 * Returns the number of items per page.
	 *
	 * @access  public
	 * @return  int
	 */
	public function itemsPerPage(): int;

	/**
	 * Returns the current page.
	 *
	 * @access  public
	 * @return  int
	 */
	public function currentPage(): int;

	/**
	 * Returns the number pages.
	 *
	 * @access  public
	 * @return  int
	 */
	public function numberOfPages(): int;

	/**
	 * Returns the limit.
	 *
	 * @access  public
	 * @return  int
	 */
	public function limit(): int;

	/**
	 * Returns the offset.
	 *
	 * @access  public
	 * @return  int
	 */
	public function offset(): int;
}