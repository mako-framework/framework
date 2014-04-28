<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pagination;

use \mako\http\Request;
use \mako\http\routing\URLBuilder;
use \mako\view\renderers\RendererInterface;

/**
 * Pagination class.
 *
 * @author  Frederic G. Østby
 */

class Pagination
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Request instance.
	 * 
	 * @var \mako\http\Request
	 */

	protected $request;

	/**
	 * URL builder instance.
	 * 
	 * @var \mako\http\request\URLBuilder
	 */

	protected $urlBuilder;

	/**
	 * View renderer instance.
	 * 
	 * @var \mako\view\renderers\RendererInterface
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
	 * @param
	 * @param
	 * @param
	 * @param
	 * @param
	 */

	public function __construct(Request $request, URLBuilder $urlBuilder, RendererInterface $view, $count, array $config)
	{
		$this->request = $request;

		$this->urlBuilder = $urlBuilder;

		$this->view = $view;

		$this->count = $count;

		$this->key = $config['page_key'];

		$this->itemsPerPage = max($config['items_per_page'], 1);

		$this->maxPageLinks = $config['max_page_links'];

		$this->currentPage = max((int) $this->request->get($this->key, 1), 1);

		$this->pages = ceil(($this->count / $this->itemsPerPage));
		
		$this->currentPage = min($this->currentPage, $this->pages);

		$this->offset = ($this->currentPage - 1) * $this->itemsPerPage;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the view renderer instance.
	 * 
	 * @access  public
	 * @return  \mako\view\renderers\RendererInterface
	 */

	public function getView()
	{
		return $this->view;
	}
	
	/**
	 * Returns the total amount of items.
	 *
	 * @access  public
	 * @return  int
	 */

	public function total()
	{
		return $this->count;
	}

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
	 * Returns the number of the first item on the paginator
	 *
	 * @access  public
	 * @return  int
	 */

	public function from()
	{
		return $this->count ? ($this->currentPage - 1) * $this->itemsPerPage + 1 : 0;
	}

	/**
	 * Returns the number of the last item on the paginator
	 *
	 * @access  public
	 * @return  int
	 */

	public function to()
	{
		return min($this->count, $this->currentPage * $this->itemsPerPage);
	}

	/**
	 * Builds and returns the pagination array.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function paginate()
	{
		$pagination = [];

		$pagination['count'] = $this->pages;
		
		$pagination['total'] = $this->total();

		$pagination['from'] = $this->from();

		$pagination['to'] = $this->to();

		$params = $this->request->get();

		if($this->currentPage > 1)
		{
			$pagination['first']    = $this->urlBuilder->current(array_merge($params, [$this->key => 1]));
			$pagination['previous'] = $this->urlBuilder->current(array_merge($params, [$this->key => ($this->currentPage - 1)]));
		}

		if($this->currentPage < $this->pages)
		{
			$pagination['last'] = $this->urlBuilder->current(array_merge($params, [$this->key => $this->pages]));
			$pagination['next'] = $this->urlBuilder->current(array_merge($params, [$this->key => ($this->currentPage + 1)]));
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

		$pagination['pages'] = [];

		for($i = $start + 1; $i <= $end; $i++)
		{
			$pagination['pages'][] = 
			[
				'url'        => $this->urlBuilder->current(array_merge($params, [$this->key => $i])),
				'number'     => $i,
				'is_current' => ($i == $this->currentPage),
			];
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
		foreach($this->paginate() as $name => $value)
		{
			$this->view->assign($name, $value);
		}

		return $this->view->render();
	}
}
