<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pagination;

use mako\http\Request;
use mako\http\routing\URLBuilder;
use mako\view\ViewFactory;
use RuntimeException;

use function ceil;
use function json_encode;
use function max;

/**
 * Pagination class.
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
	 * Query parameter cache.
	 *
	 * @var array
	 */
	protected $params;

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
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function items(): int
	{
		return $this->items;
	}

	/**
	 * {@inheritDoc}
	 */
	public function itemsPerPage(): int
	{
		return $this->itemsPerPage;
	}

	/**
	 * {@inheritDoc}
	 */
	public function currentPage(): int
	{
		return $this->currentPage;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isValidPage(): bool
	{
		return $this->currentPage <= $this->pages;
	}

	/**
	 * {@inheritDoc}
	 */
	public function numberOfPages(): int
	{
		return $this->pages;
	}

	/**
	 * {@inheritDoc}
	 */
	public function limit(): int
	{
		return $this->itemsPerPage;
	}

	/**
	 * {@inheritDoc}
	 */
	public function offset(): int
	{
		return $this->offset;
	}

	/**
	 * Sets the request instance.
	 *
	 * @param \mako\http\Request $request Request
	 */
	public function setRequest(Request $request): void
	{
		$this->request = $request;
	}

	/**
	 * Sets the URL builder instance.
	 *
	 * @param \mako\http\routing\URLBuilder $urlBuilder URL builder instance
	 */
	public function setURLBuilder(URLBuilder $urlBuilder): void
	{
		$this->urlBuilder = $urlBuilder;
	}

	/**
	 * Sets the view factory builder instance.
	 *
	 * @param \mako\view\ViewFactory $viewFactory View factory instance
	 */
	public function setViewFactory(ViewFactory $viewFactory): void
	{
		$this->viewFactory = $viewFactory;
	}

	/**
	 * Builds a url to the desired page.
	 *
	 * @param  int    $page Page
	 * @return string
	 */
	protected function buildPageUrl(int $page): string
	{
		if($this->params === null)
		{
			$this->params = $this->request->getQuery()->all();
		}

		return $this->urlBuilder->current([$this->options['page_key'] => $page] + $this->params);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		$pagination =
		[
			'current_page'    => $this->currentPage,
			'number_of_pages' => $this->pages,
			'items'           => $this->items,
			'items_per_page'  => $this->itemsPerPage,
		];

		if($this->urlBuilder !== null && $this->request !== null)
		{
			$pagination +=
			[

				'first'    => $this->buildPageUrl(1),
				'last'     => $this->buildPageUrl($this->pages),
				'next'     => $this->currentPage < $this->pages ? $this->buildPageUrl($this->currentPage + 1) : null,
				'previous' => $this->currentPage > 1 ? $this->buildPageUrl($this->currentPage - 1) : null,
			];
		}

		return $pagination;
	}

	/**
	 * Returns data which can be serialized by json_encode().
	 *
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toJson(int $options = 0): string
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * Builds and returns the pagination array.
	 *
	 * @return array
	 */
	public function pagination(): array
	{
		if(empty($this->pagination))
		{
			if(empty($this->request))
			{
				throw new RuntimeException('A [ Request ] instance is required to generate the pagination array.');
			}

			if(empty($this->urlBuilder))
			{
				throw new RuntimeException('A [ URLBuilder ] instance is required to generate the pagination array.');
			}

			$pagination = $this->toArray();

			if($this->options['max_page_links'] !== 0)
			{
				if($this->pages > $this->options['max_page_links'])
				{
					$start = (int) max(($this->currentPage) - ceil($this->options['max_page_links'] / 2), 0);

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
						'url'        => $this->buildPageUrl($i),
						'number'     => $i,
						'is_current' => $i === $this->currentPage,
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
	 * @param  string $view Pagination view
	 * @return string
	 */
	public function render(string $view): string
	{
		if(empty($this->viewFactory))
		{
			throw new RuntimeException('A [ ViewFactory ] instance is required to render pagination views.');
		}

		return $this->viewFactory->create($view, $this->pagination())->render();
	}
}
