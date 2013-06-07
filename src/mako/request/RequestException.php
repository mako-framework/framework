<?php

namespace mako\request;

use \Exception;

/**
 * Request exception.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class RequestException extends \RuntimeException
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
	 * @param   int     $code         Exception code
	 * @param   string  $message      Exception message
	 * @param   \Exception $previous  (optional) Previous exception
	 */

	public function __construct($code, $message = null, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	// Nothing here
}

/** -------------------- End of file -------------------- **/