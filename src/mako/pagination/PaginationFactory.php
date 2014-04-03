<?php

namespace mako\pagination;

use \mako\http\Request;
use \mako\http\routing\URLBuilder;
use \mako\pagination\Pagination;
use \mako\view\ViewFactory;

/**
 * Pagination factory.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class PaginationFactory
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
	 * View factory instance.
	 * 
	 * @var \mako\view\ViewFactory
	 */

	protected $viewFactory;

	/**
	 * Configuration.
	 * 
	 * @var array
	 */

	protected $config;

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
	 */

	public function __construct(Request $request, URLBuilder $urlBuilder, ViewFactory $viewFactory, array $config)
	{
		$this->request = $request;

		$this->urlBuilder = $urlBuilder;

		$this->viewFactory = $viewFactory;

		$this->config = $config;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Creates and returns a pagination instance.
	 * 
	 * @access  public
	 * @param   string                       $view    Pagination partial
	 * @param   int                          $count   Number of items
	 * @param   array                        $config  (optional) Override configuration
	 * @return  \mako\pagination\Pagination
	 */

	public function create($partial, $count, array $config = [])
	{
		$config = $config + $this->config;

		return new Pagination($this->request, $this->urlBuilder, $this->viewFactory->create($partial), $count, $config);
	}
}

