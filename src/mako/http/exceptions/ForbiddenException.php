<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Forbidden exception.
 */
class ForbiddenException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	protected string|null $defaultMessage = 'You don\'t have permission to access the requested resource.';

	/**
	 * Constructor.
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(403, $message, $previous);
	}
}
