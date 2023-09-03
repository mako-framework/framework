<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Service unavailable exception.
 */
class ServiceUnavailableException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	protected string $defaultMessage = 'The service is currently unavailable.';

	/**
	 * Constructor.
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(503, $message, $previous);
	}
}
