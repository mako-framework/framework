<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pagination;

use RuntimeException;

use mako\http\Request;
use mako\http\routing\URLBuilder;
use mako\view\ViewFactory;

/**
 * Pagination class.
 *
 * @author  Frederic G. Østby
 */

class Pagination
{
	/**
	 * Request instance.
	 *
	 * @var \mako\http\Request
	 */

	protected $request;

	/**
	 * Number of items.
	 *
	 * @var int
	 */

	protected $count;

	/**
	 * Configuration.
	 *
	 * @var array
	 */

	protected $config =
	[
		'page_key'       => 'page',
		'max_page_links' => 5,
		'items_per_page' => 20,
	];

	/**
	 * URL builder instance.
	 *
	 * @var \mako\http\request\URLBuilder
	 */

	protected $urlBuilder;

	/**
	 * View factory instance.
	 *
	 * @var \mako\view\ViewFactory
	 */

	protected $viewFactory;

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

	/**
	 * Pagination.
	 *
	 * @var array
	 */

	protected $pagination = [];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\http\Request             $request      Request
	 * @param   int                            $count        Item count
	 * @param   array                          $config       Configuration
	 * @param   \mako\http\routing\URLBuilder  $urlBuilder   URL builder instance
	 * @param   \mako\view\ViewFactory         $viewFactory  View factory instance
	 */

	public function __construct(Request $request, $count, array $config = [], URLBuilder $urlBuilder = null, ViewFactory $viewFactory = null)
	{
		$this->request = $request;

		$this->count = $count;

		$this->config = $config + $this->config;

		$this->urlBuilder = $urlBuilder;

		$this->viewFactory = $viewFactory;

		// Get the current page

		$this->currentPage = max((int) $this->request->get($this->config['page_key'], 1), 1);

		// Calculate the number of pages

		$this->pages = ceil(($count / $this->config['items_per_page']));

		// Calculate the offset

		$this->offset = ($this->currentPage - 1) * $this->config['items_per_page'];
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
		return $this->config['items_per_page'];
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
	 * @access  public
	 * @return  array
	 */

	public function paginate()
	{
		if(empty($this->pagination))
		{
			if(empty($this->urlBuilder))
			{
				throw new RuntimeException(vsprintf("%s(): A [ URLBuilder ] instance is required to render pagination views.", [__METHOD__]));
			}

			$pagination = [];

			$pagination['count'] = $this->pages;

			$params = $this->request->get();

			if($this->currentPage > 1)
			{
				$pagination['first']    = $this->urlBuilder->current(array_merge($params, [$this->config['page_key'] => 1]));
				$pagination['previous'] = $this->urlBuilder->current(array_merge($params, [$this->config['page_key'] => ($this->currentPage - 1)]));
			}

			if($this->currentPage < $this->pages)
			{
				$pagination['last'] = $this->urlBuilder->current(array_merge($params, [$this->config['page_key'] => $this->pages]));
				$pagination['next'] = $this->urlBuilder->current(array_merge($params, [$this->config['page_key'] => ($this->currentPage + 1)]));
			}

			if($this->config['max_page_links'] !== 0)
			{
				if($this->pages > $this->config['max_page_links'])
				{
					$start = max(($this->currentPage) - ceil($this->config['max_page_links'] / 2), 0);

					$end = $start + $this->config['max_page_links'];

					if($end > $this->pages)
					{
						$end = $this->pages;
					}

					if($start > ($end - $this->config['max_page_links']))
					{
						$start = $end - $this->config['max_page_links'];
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
						'url'        => $this->urlBuilder->current(array_merge($params, [$this->config['page_key'] => $i])),
						'number'     => $i,
						'is_current' => ($i == $this->currentPage),
					];
				}
			}

			$this->pagination = $pagination;
		}

		return $this->pagination;
	}

	/**
	 * Renders and returns the pagination partial.
	 *
	 * @access  public
	 * @param   string  $view  Pagination view
	 * @return  string
	 */

	public function render($view)
	{
		if(empty($this->viewFactory))
		{
			throw new RuntimeException(vsprintf("%s(): A [ ViewFactory ] instance is required to render pagination views.", [__METHOD__]));
		}

		return $this->viewFactory->create($view, $this->paginate())->render();
	}
}