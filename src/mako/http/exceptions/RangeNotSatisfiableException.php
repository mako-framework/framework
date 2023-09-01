<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Not found exception.
 */
class RangeNotSatisfiableException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	protected string|null $defaultMessage = 'The requested range is not satisfiable.';

	/**
	 * Constructor.
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(416, $message, $previous);
	}
}
