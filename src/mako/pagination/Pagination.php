<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pagination;

use mako\http\Request;
use mako\http\routing\URLBuilder;
use mako\pagination\exceptions\PaginationException;
use mako\view\ViewFactory;
use Override;

use function ceil;
use function json_encode;
use function max;

/**
 * Pagination class.
 */
class Pagination implements PaginationInterface
{
	/**
	 * Options.
	 */
	protected array $options = [
		'page_key'       => 'page',
		'max_page_links' => 5,
	];

	/**
	 * Number of pages.
	 */
	protected int $pages;

	/**
	 * Offset.
	 */
	protected int $offset;

	/**
	 * Request instance.
	 */
	protected ?Request $request = null;

	/**
	 * Query parameter cache.
	 */
	protected ?array $params = null;

	/**
	 * URL builder instance.
	 */
	protected ?URLBuilder $urlBuilder = null;

	/**
	 * View factory instance.
	 */
	protected ?ViewFactory $viewFactory = null;

	/**
	 * Pagination.
	 */
	protected array $pagination = [];

	/**
	 * {@inheritDoc}
	 */
	public function __construct(
		protected int $items,
		protected int $itemsPerPage,
		protected int $currentPage,
		array $options = []
	) {
		$this->options = $options + $this->options;

		// Calculate the number of pages

		$this->pages = (int) ceil(($this->items / $this->itemsPerPage));

		// Calculate the offset

		$this->offset = ($this->currentPage - 1) * $this->itemsPerPage;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function items(): int
	{
		return $this->items;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function itemsPerPage(): int
	{
		return $this->itemsPerPage;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function currentPage(): int
	{
		return $this->currentPage;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function isValidPage(): bool
	{
		return $this->currentPage <= $this->pages;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function numberOfPages(): int
	{
		return $this->pages;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function limit(): int
	{
		return $this->itemsPerPage;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function offset(): int
	{
		return $this->offset;
	}

	/**
	 * Sets the request instance.
	 */
	public function setRequest(Request $request): void
	{
		$this->request = $request;
	}

	/**
	 * Sets the URL builder instance.
	 */
	public function setURLBuilder(URLBuilder $urlBuilder): void
	{
		$this->urlBuilder = $urlBuilder;
	}

	/**
	 * Sets the view factory builder instance.
	 */
	public function setViewFactory(ViewFactory $viewFactory): void
	{
		$this->viewFactory = $viewFactory;
	}

	/**
	 * Builds a url to the desired page.
	 */
	protected function buildPageUrl(int $page): string
	{
		if ($this->params === null) {
			$this->params = $this->request->query->all();
		}

		return $this->urlBuilder->current([$this->options['page_key'] => $page] + $this->params);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function toArray(): array
	{
		$pagination = [
			'current_page'    => $this->currentPage,
			'number_of_pages' => $this->pages,
			'items'           => $this->items,
			'items_per_page'  => $this->itemsPerPage,
		];

		if ($this->urlBuilder !== null && $this->request !== null) {
			$pagination += [
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
	 */
	public function jsonSerialize(): mixed
	{
		return $this->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function toJson(int $options = 0): string
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * Builds and returns the pagination array.
	 */
	public function pagination(): array
	{
		if (empty($this->pagination)) {
			if (empty($this->request)) {
				throw new PaginationException('A [ Request ] instance is required to generate the pagination array.');
			}

			if (empty($this->urlBuilder)) {
				throw new PaginationException('A [ URLBuilder ] instance is required to generate the pagination array.');
			}

			$pagination = $this->toArray();

			if ($this->options['max_page_links'] !== 0) {
				if ($this->pages > $this->options['max_page_links']) {
					$start = (int) max(($this->currentPage) - ceil($this->options['max_page_links'] / 2), 0);

					$end = $start + $this->options['max_page_links'];

					if ($end > $this->pages) {
						$end = $this->pages;
					}

					if ($start > ($end - $this->options['max_page_links'])) {
						$start = $end - $this->options['max_page_links'];
					}
				}
				else {
					$start = 0;

					$end = $this->pages;
				}

				$pagination['pages'] = [];

				for ($i = $start + 1; $i <= $end; $i++) {
					$pagination['pages'][] = [
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
	 */
	public function render(string $view): string
	{
		if (empty($this->viewFactory)) {
			throw new PaginationException('A [ ViewFactory ] instance is required to render pagination views.');
		}

		return $this->viewFactory->create($view, $this->pagination())->render();
	}
}
