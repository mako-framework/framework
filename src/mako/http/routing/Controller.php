<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use mako\syringe\ContainerAwareTrait;

/**
 * Base controller that all application controllers must extend.
 *
 * @author  Frederic G. Østby
 */

abstract class Controller
{
	use ContainerAwareTrait;

	/**
	 * This method runs before the action.
	 *
	 * @access  public
	 */

	public function beforeFilter()
	{

	}

	/**
	 * This method runs after the action.
	 *
	 * @access  public
	 */

	public function afterFilter()
	{

	}
}