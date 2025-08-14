<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pagination;

use mako\http\Request;
use mako\http\routing\URLBuilder;
use mako\view\ViewFactory;
use Override;

use function max;

/**
 * Pagination factory.
 */
class PaginationFactory implements PaginationFactoryInterface
{
	/**
	 * URL builder instance.
	 */
	protected ?URLBuilder $urlBuilder = null;

	/**
	 * View factory instance.
	 */
	protected ?ViewFactory $viewFactory = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Request $request,
		protected array $options = []
	) {
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
	 * {@inheritDoc}
	 */
	#[Override]
	public function create(int $items, ?int $itemsPerPage = null, array $options = []): PaginationInterface
	{
		$itemsPerPage ??= $this->options['items_per_page'];

		$options = $options + $this->options;

		$currentPage = max((int) $this->request->query->get($options['page_key'], 1), 1);

		$pagination = new Pagination($items, $itemsPerPage, $currentPage, $options);

		$pagination->setRequest($this->request);

		if (!empty($this->urlBuilder)) {
			$pagination->setURLBuilder($this->urlBuilder);
		}

		if (!empty($this->viewFactory)) {
			$pagination->setViewFactory($this->viewFactory);
		}

		return $pagination;
	}
}
