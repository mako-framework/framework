<?php

namespace mako;

use \mako\URL;
use \mako\View;
use \mako\Config;
use \mako\ErrorHandler;
use \Exception;

/**
 * Pagination class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Pagination
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Pagination view.
	 * 
	 * @var string
	 */

	protected $view;

	/**
	 * Number of items.
	 * 
	 * @var int
	 */

	protected $count;

	/**
	 * Name of the $_GET key holding the current page number.
	 *
	 * @var string
	 */

	protected $key;
	
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
	 * Offset.
	 *
	 * @var int
	 */

	protected $offset;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $view          View name
	 * @param   int     $cont          Number of items
	 * @param   int     $itemsPerPage  (optional) Number of items to display on a page
	 */

	public function __construct($view, $count, $itemsPerPage = null)
	{
		$config = Config::get('pagination');

		$this->view         = $view;
		$this->count        = $count;
		$this->key          = $config['page_key'];
		$this->itemsPerPage = ($itemsPerPage === null) ? max($config['items_per_page'], 1) : max($itemsPerPage, 1);
		$this->maxPageLinks = $config['max_page_links'];
		$this->currentPage  = max((int) (isset($_GET[$this->key]) ? $_GET[$this->key] : 1), 1);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the limit.
	 * 
	 * @access  public
	 * @return  int
	 */

	public function limit()
	{
		return $this->itemsPerPage;
	}

	/**
	 * Calculates the offset and number of pages and returns the offset.
	 *
	 * @access  public
	 * @return  int
	 */

	public function offset()
	{
		$this->pages  = ceil(($this->count / $this->itemsPerPage));
		$this->offset = ($this->currentPage - 1) * $this->itemsPerPage;

		return $this->offset;
	}

	/**
	 * Builds and returns the pagination array.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function paginate()
	{
		$pagination = array();

		$pagination['count'] = $this->pages;

		$params = isset($_GET) ? $_GET : array();

		if($this->currentPage > 1)
		{
			$pagination['first']    = URL::current(array_merge($params, array($this->key => 1)));
			$pagination['previous'] = URL::current(array_merge($params, array($this->key => $this->currentPage - 1)));
		}

		if($this->currentPage < $this->pages)
		{
			$pagination['last'] = URL::current(array_merge($params, array($this->key => $this->pages)));
			$pagination['next'] = URL::current(array_merge($params, array($this->key => ($this->currentPage + 1))));
		}

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

		$pagination['pages'] = array();

		for($i = $start + 1; $i <= $end; $i++)
		{
			$pagination['pages'][] = array
			(
				'url'        => URL::current(array_merge($params, array($this->key => $i))),
				'number'     => $i,
				'is_current' => ($i == $this->currentPage),
			);
		}

		return $pagination;
	}

	/**
	 * Returns the rendered pagination view.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function render()
	{
		if($this->offset === null)
		{
			$this->offset();
		}

		return View::factory($this->view, $this->paginate())->render();
	}

	/**
	 * Method that magically converts the pagination object into a string.
	 *
	 * @access  public
	 * @return  string
	 */

	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch(Exception $e)
		{
			ErrorHandler::handler($e);
		}
	}
}

/** -------------------- End of file -------------------- **/