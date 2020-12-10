<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use RuntimeException;
use Throwable;

/**
 * HTTP exception.
 */
class HttpException extends RuntimeException
{
	/**
	 * Default message.
	 *
	 * @var string|null
	 */
	protected $defaultMessage;

	/**
	 * Constructor.
	 *
	 * @param int             $code     Exception code
	 * @param string          $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(int $code, string $message = '', ?Throwable $previous = null)
	{
		parent::__construct($message ?: (string) $this->defaultMessage, $code, $previous);
	}
}
