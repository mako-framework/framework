<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\error\handlers;

use \Exception;

/**
 * Store interface.
 *
 * @author  Frederic G. Østby
 */

interface HandlerInterface
{
	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \Exception  $exception  Exception
	 */

	public function __construct(Exception $exception);

	/**
	 * Handles the exception.
	 * 
	 * @access  public
	 * @param   boolean  $showDetails  (optional) Show error details?
	 * @return  boolean
	 */
	
	public function handle($showDetails = true);
}