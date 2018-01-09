<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pagination;

use mako\http\Request;
use mako\http\routing\URLBuilder;
use mako\pagination\Pagination;
use mako\pagination\PaginationFactoryInterface;
use mako\pagination\PaginationInterface;
use mako\view\ViewFactory;

/**
 * Pagination factory.
 *
 * @author Frederic G. Østby
 * @author Yamada Taro
 */
class PaginationFactory implements PaginationFactoryInterface
{
	/**
	 * Request instance.
	 *
	 * @var \mako\http\Request
	 */
	protected $request;

	/**
	 * Options.
	 *
	 * @var array
	 */
	protected $options;

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
	 * Constructor.
	 *
	 * @param \mako\http\Request $request Request
	 * @param array              $options Options
	 */
	public function __construct(Request $request, array $options = [])
	{
		$this->request = $request;

		$this->options = $options;
	}

	/**
	 * Sets the URL builder instance.
	 *
	 * @param \mako\http\routing\URLBuilder $urlBuilder URL builder instance
	 */
	public function setURLBuilder(URLBuilder $urlBuilder)
	{
		$this->urlBuilder = $urlBuilder;
	}

	/**
	 * Sets the view factory builder instance.
	 *
	 * @param \mako\view\ViewFactory $viewFactory View factory instance
	 */
	public function setViewFactory(ViewFactory $viewFactory)
	{
		$this->viewFactory = $viewFactory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function create(int $items, int $itemsPerPage = null, array $options = []): PaginationInterface
	{
		$itemsPerPage = $itemsPerPage ?? $this->options['items_per_page'];

		$options = $options + $this->options;

		$currentPage = max((int) $this->request->getQuery()->get($options['page_key'], 1), 1);

		$pagination = new Pagination($items, $itemsPerPage, $currentPage, $options);

		$pagination->setRequest($this->request);

		$pagination->setURLBuilder($this->urlBuilder);

		$pagination->setViewFactory($this->viewFactory);

		return $pagination;
	}
}
