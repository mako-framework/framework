<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pagination;

use mako\http\Request;
use mako\http\routing\URLBuilder;
use mako\pagination\Pagination;
use mako\view\ViewFactory;

/**
 * Pagination factory.
 *
 * @author  Frederic G. Østby
 */

class PaginationFactory
{
	/**
	 * Request instance.
	 *
	 * @var \mako\http\Request
	 */

	protected $request;

	/**
	 * Configuration.
	 *
	 * @var array
	 */

	protected $config;

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
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\http\Request             $request      Request
	 * @param   array                          $config       Configuration
	 * @param   \mako\http\routing\URLBuilder  $urlBuilder   URL builder instance
	 * @param   \mako\view\ViewFactory         $viewFactory  View factory instance
	 */

	public function __construct(Request $request, array $config = [], URLBuilder $urlBuilder = null, ViewFactory $viewFactory = null)
	{
		$this->request = $request;

		$this->config = $config;

		$this->urlBuilder = $urlBuilder;

		$this->viewFactory = $viewFactory;
	}

	/**
	 * Sets the URL builder instance.
	 *
	 * @access  public
	 * @param   \mako\http\request\URLBuilder  $urlBuilder  URL builder instance
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
	 * Creates and returns a pagination instance.
	 *
	 * @access  public
	 * @param   int                          $count   Number of items
	 * @param   array                        $config  Override configuration
	 * @return  \mako\pagination\Pagination
	 */

	public function create($count, array $config = [])
	{
		$config = $config + $this->config;

		return new Pagination($this->request, $count, $config, $this->urlBuilder, $this->viewFactory);
	}
}