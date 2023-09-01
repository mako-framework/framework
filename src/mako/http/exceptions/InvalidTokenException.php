<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Invalid token exception.
 */
class InvalidTokenException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	protected string|null $defaultMessage = 'An invalid or expired token was provided.';

	/**
	 * Constructor.
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(498, $message, $previous);
	}
}
