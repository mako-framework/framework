<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use RuntimeException;
use Throwable;

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
	 * @param int             $code     Exception code
	 * @param string|null     $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(int $code, string $message = null, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
