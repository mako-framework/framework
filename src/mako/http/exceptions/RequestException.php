<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;
use RuntimeException;

/**
 * Request exception.
 *
 * @author Frederic G. Østby
 */
class RequestException extends RuntimeException
{
	/**
	 * Constructor.
	 *
	 * @access public
	 * @param int        $code     Exception code
	 * @param string     $message  Exception message
	 * @param \Throwable $previous Previous exception
	 */
	public function __construct(int $code, string $message = null, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
