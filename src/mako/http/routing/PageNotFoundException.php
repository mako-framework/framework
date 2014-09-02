<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use \Exception;

use \mako\http\RequestException;

/**
 * Page not found exception.
 *
 * @author  Frederic G. Østby
 */

class PageNotFoundException extends RequestException
{
	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $message      Exception message
	 * @param   \Exception $previous  (optional) Previous exception
	 */

	public function __construct($message = null, Exception $previous = null)
	{
		parent::__construct(404, $message, $previous);
	}
}