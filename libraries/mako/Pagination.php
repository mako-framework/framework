<?php

namespace mako;

use \mako\URL;
use \mako\Config;
use \mako\I18n;

/**
 * Pagination class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2012 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Pagination
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Name of the $_GET key holding the current page number.
	 *
	 * @var string
	 */

	protected $key;

	/**
	 * Offset.
	 *
	 * @var int
	 */

	protected $offset;
	
	/**
	 * Current page.
	 *
	 * @var int
	 */

	protected $currentPage;

	/**
	 * Number of pages.
	 *
	 * @var int
	 */

	protected $pages;
	
	/**
	 * Number of items per page.
	 *
	 * @var int
	 */
	
	protected $itemsPerPage;
	
	/**
	 * Maximum number of page links.
	 *
	 * @var int
	 */
	
	protected $maxPageLinks;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 */

	public function __construct()
	{
		$config = Config::get('pagination');
		
		$this->key          = $config['page_key'];
		$this->currentPage  = max((int) (isset($_GET[$this->key]) ? $_GET[$this->key] : 1), 1);
		$this->itemsPerPage = $config['items_per_page'];
		$this->maxPageLinks = $config['max_page_links'];
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Calculates the offset and number of pages and returns the offset.
	 *
	 * @access  public
	 * @param   int     $itemCount     Number of items
	 * @param   int     $itemsPerPage  Number of items to display on each page
	 * @return  int
	 */

	public function offset($itemCount, $itemsPerPage = null)
	{
		$itemsPerPage = ($itemsPerPage === null) ? max($this->itemsPerPage, 1) : max($itemsPerPage, 1);
		$this->pages  = ceil(($itemCount / $itemsPerPage));
		$this->offset = ($this->currentPage - 1) * $itemsPerPage;

		return $this->offset;
	}

	/**
	 * Returns an associative array of pagination links.
	 *
	 * @access  public
	 * @param   string  $url        (optional) URL segments
	 * @param   array   $params     (optional) Associative array used to build URL-encoded query string
	 * @param   string  $separator  (optional) Argument separator
	 * @return  array
	 */

	public function links($url = '', array $params = array(), $separator = '&amp;')
	{
		$links = array();
				
		// Number of pages
		
		$links['num_pages'] = I18n::get('pagination.pages', array($this->pages));
		
		// First and previous page
		
		if($this->currentPage > 1)
		{
			$links['first_page'] = array
			(
				'name' => I18n::get('pagination.first'), 
				'url'  => URL::to($url, array_merge($params, array($this->key => 1)), $separator),
			);
			
			$links['previous_page'] = array
			(
				'name' => '&laquo;',
				'url'  => URL::to($url, array_merge($params, array($this->key => ($this->currentPage - 1))), $separator),
			);
		}
		
		// Last and next page
		
		if($this->currentPage < $this->pages)
		{
			$links['last_page'] = array
			(
				'name' => I18n::get('pagination.last'),
				'url'  => URL::to($url, array_merge($params, array($this->key => $this->pages)), $separator),
			);
			
			$links['next_page'] = array
			(
				'name' => '&raquo;',
				'url'  => URL::to($url, array_merge($params, array($this->key => ($this->currentPage + 1))), $separator),
			);
		}
		
		// Page links
		
		if($this->pages > $this->maxPageLinks)
		{
			$start = max(($this->currentPage) - ceil($this->maxPageLinks / 2), 0);

			$end = $start + $this->maxPageLinks;

			if($end > $this->pages)
			{
				$end = $this->pages;
			}

			if($start > ($end - $this->maxPageLinks))
			{
				$start = $end - $this->maxPageLinks;
			}
		}
		else
		{
			$start = 0;

			$end = $this->pages;
		}
		
		for($i = $start + 1; $i <= $end; $i++)
		{
			$links['pages'][] = array
			(
				'name'    => $i,
				'url'     => URL::to($url, array_merge($params, array($this->key => $i)), $separator),
				'is_current' => ($i == $this->currentPage),
			);
		}
					
		return $links;
	}
}

/** -------------------- End of file --------------------**/