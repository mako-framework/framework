<?php

namespace mako\http\routing;

use \Exception;

/**
 * Page not found exception.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class PageNotFoundException extends \mako\http\RequestException
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

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

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	// Nothing here
}

