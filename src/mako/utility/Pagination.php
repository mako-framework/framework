<?php

namespace mako\utility;

use \mako\view\View;
use \mako\core\Config;
use \mako\http\Input;
use \mako\http\Request;
use \mako\http\routing\URL;
use \mako\core\errors\ErrorHandler;
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
	 * Request instance.
	 * 
	 * @var \mako\http\Request
	 */

	protected $request;
	
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
	 * @param   string              $view          View name
	 * @param   int                 $cont          Number of items
	 * @param   int                 $itemsPerPage  (optional) Number of items to display on a page
	 * @param   \mako\http\Request  $request       (optional) Request instance
	 */

	public function __construct($view, $count, $itemsPerPage = null, Request $request = null)
	{
		$config = Config::get('pagination');

		$this->request = $request ?: Request::main();

		$this->view         = $view;
		$this->count        = $count;
		$this->key          = $config['page_key'];
		$this->itemsPerPage = ($itemsPerPage === null) ? max($config['items_per_page'], 1) : max($itemsPerPage, 1);
		$this->maxPageLinks = $config['max_page_links'];
		$this->currentPage  = max((int) $this->request->get($this->key, 1), 1);
		$this->pages        = ceil(($this->count / $this->itemsPerPage));
		$this->offset       = ($this->currentPage - 1) * $this->itemsPerPage;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the total amount of pages.
	 * 
	 * @access  public
	 * @return  int
	 */

	public function pages()
	{
		return $this->pages;
	}

	/**
	 * Returns the current page.
	 * 
	 * @access  public
	 * @return  int
	 */

	public function currentPage()
	{
		return $this->currentPage;
	}

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
	 * Returns the offset.
	 *
	 * @access  public
	 * @return  int
	 */

	public function offset()
	{
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

		$params = $this->request->get();

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

		return (new View($this->view, $this->paginate()))->render();
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