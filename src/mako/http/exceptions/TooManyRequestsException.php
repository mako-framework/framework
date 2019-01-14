<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Too many requests exception.
 *
 * @author Frederic G. Østby
 */
class TooManyRequestsException extends HttpException
{
	/**
	 * {@inheritdoc}
	 */
	protected $defaultMessage = 'You have made too many requests to the server.';

	/**
	 * Constructor.
	 *
	 * @param string          $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(429, $message, $previous);
	}
}
