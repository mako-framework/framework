<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\error\handlers;

/**
 * Store interface.
 *
 * @author  Frederic G. Østby
 */
interface HandlerInterface
{
	/**
	 * Handles the exception.
	 *
	 * @access  public
	 * @param   bool       $showDetails  Show error details?
	 * @return  void|bool
	 */
	public function handle(bool $showDetails = true);
}