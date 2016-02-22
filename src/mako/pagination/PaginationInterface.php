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
	public function __construct($items, $itemsPerPage, $currentPage, array $options = []);

	/**
	 * Returns the number of items.
	 *
	 * @access  public
	 * @return  int
	 */
	public function items();

	/**
	 * Returns the number of items per page.
	 *
	 * @access  public
	 * @return  int
	 */
	public function itemsPerPage();

	/**
	 * Returns the current page.
	 *
	 * @access  public
	 * @return  int
	 */
	public function currentPage();

	/**
	 * Returns the number pages.
	 *
	 * @access  public
	 * @return  int
	 */
	public function numberOfPages();

	/**
	 * Returns the limit.
	 *
	 * @access  public
	 * @return  int
	 */
	public function limit();

	/**
	 * Returns the offset.
	 *
	 * @access  public
	 * @return  int
	 */
	public function offset();
}