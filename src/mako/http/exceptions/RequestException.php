<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Exception;
use RuntimeException;

/**
 * Request exception.
 *
 * @author  Frederic G. Østby
 */

class RequestException extends RuntimeException
{
	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   int     $code         Exception code
	 * @param   string  $message      Exception message
	 * @param   \Exception $previous  Previous exception
	 */

	public function __construct($code, $message = null, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}