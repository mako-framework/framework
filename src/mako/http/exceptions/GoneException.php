<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Gone exception.
 */
class GoneException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	protected string $defaultMessage = 'The resource you requested is no longer available and will not be available again.';

	/**
	 * Constructor.
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(410, $message, $previous);
	}
}
