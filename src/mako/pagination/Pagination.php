<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pagination;

use RuntimeException;

use mako\http\Request;
use mako\http\routing\URLBuilder;
use mako\pagination\PaginationInterface;
use mako\view\ViewFactory;

/**
 * Pagination class.
 *
 * @author  Frederic G. Østby
 * @author  Yamada Taro
 */
class Pagination implements PaginationInterface
{
	/**
	 * Number of items.
	 *
	 * @var int
	 */
	protected $items;

	/**
	 * Number of items per page.
	 *
	 * @var int
	 */
	protected $itemsPerPage;

	/**
	 * Current page.
	 *
	 * @var int
	 */
	protected $currentPage;

	/**
	 * Options.
	 *
	 * @var array
	 */
	protected $options =
	[
		'page_key'       => 'page',
		'max_page_links' => 5,
	];

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
	 * Request instance.
	 *
	 * @var \mako\http\Request
	 */
	protected $request;

	/**
	 * URL builder instance.
	 *
	 * @var \mako\http\routing\URLBuilder
	 */
	protected $urlBuilder;

	/**
	 * View factory instance.
	 *
	 * @var \mako\view\ViewFactory
	 */
	protected $viewFactory;

	/**
	 * Pagination.
	 *
	 * @var array
	 */
	protected $pagination = [];

	/**
	 * {@inheritdoc}
	 */
	public function __construct(int $items, int $itemsPerPage, int $currentPage, array $options = [])
	{
		$this->items = $items;

		$this->itemsPerPage = $itemsPerPage;

		$this->currentPage = $currentPage;

		$this->options = $options + $this->options;

		// Calculate the number of pages

		$this->pages = (int) ceil(($this->items / $this->itemsPerPage));

		// Calculate the offset

		$this->offset = ($this->currentPage - 1) * $this->itemsPerPage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function items(): int
	{
		return $this->items;
	}

	/**
	 * {@inheritdoc}
	 */
	public function itemsPerPage(): int
	{
		return $this->itemsPerPage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function currentPage(): int
	{
		return $this->currentPage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function numberOfPages(): int
	{
		return $this->pages;
	}

	/**
	 * {@inheritdoc}
	 */
	public function limit(): int
	{
		return $this->itemsPerPage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function offset(): int
	{
		return $this->offset;
	}

	/**
	 * Sets the request instance.
	 *
	 * @access  public
	 * @param   \mako\http\Request  $request  Request
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Sets the URL builder instance.
	 *
	 * @access  public
	 * @param   \mako\http\routing\URLBuilder  $urlBuilder  URL builder instance
	 */
	public function setURLBuilder(URLBuilder $urlBuilder)
	{
		$this->urlBuilder = $urlBuilder;
	}

	/**
	 * Sets the view factory builder instance.
	 *
	 * @access  public
	 * @param   \mako\view\ViewFactory  $viewFactory  View factory instance
	 */
	public function setViewFactory(ViewFactory $viewFactory)
	{
		$this->viewFactory = $viewFactory;
	}

	/**
	 * Builds and returns the pagination array.
	 *
	 * @access  public
	 * @return  array
	 */
	public function pagination(): array
	{
		if(empty($this->pagination))
		{
			if(empty($this->request))
			{
				throw new RuntimeException(vsprintf("%s(): A [ Request ] instance is required to generate the pagination array.", [__METHOD__]));
			}

			if(empty($this->urlBuilder))
			{
				throw new RuntimeException(vsprintf("%s(): A [ URLBuilder ] instance is required to generate the pagination array.", [__METHOD__]));
			}

			$pagination = ['items' => $this->items, 'items_per_page' => $this->itemsPerPage, 'number_of_pages' => $this->pages];

			$params = $this->request->get();

			if($this->currentPage > 1)
			{
				$pagination['first']    = $this->urlBuilder->current(array_merge($params, [$this->options['page_key'] => 1]));
				$pagination['previous'] = $this->urlBuilder->current(array_merge($params, [$this->options['page_key'] => ($this->currentPage - 1)]));
			}

			if($this->currentPage < $this->pages)
			{
				$pagination['last'] = $this->urlBuilder->current(array_merge($params, [$this->options['page_key'] => $this->pages]));
				$pagination['next'] = $this->urlBuilder->current(array_merge($params, [$this->options['page_key'] => ($this->currentPage + 1)]));
			}

			if($this->options['max_page_links'] !== 0)
			{
				if($this->pages > $this->options['max_page_links'])
				{
					$start = max(($this->currentPage) - ceil($this->options['max_page_links'] / 2), 0);

					$end = $start + $this->options['max_page_links'];

					if($end > $this->pages)
					{
						$end = $this->pages;
					}

					if($start > ($end - $this->options['max_page_links']))
					{
						$start = $end - $this->options['max_page_links'];
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
						'url'        => $this->urlBuilder->current(array_merge($params, [$this->options['page_key'] => $i])),
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
	public function render(string $view): string
	{
		if(empty($this->viewFactory))
		{
			throw new RuntimeException(vsprintf("%s(): A [ ViewFactory ] instance is required to render pagination views.", [__METHOD__]));
		}

		return $this->viewFactory->create($view, $this->pagination())->render();
	}
}