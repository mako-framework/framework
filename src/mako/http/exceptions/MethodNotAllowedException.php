<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use mako\http\response\Status;
use Throwable;

/**
 * Method not allowed exception.
 */
class MethodNotAllowedException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	protected string $defaultMessage = 'The request method that was used is not supported by this resource.';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected array $allowedMethods = [],
		string $message = '',
		?Throwable $previous = null
	) {
		parent::__construct(Status::METHOD_NOT_ALLOWED, $message, $previous);
	}

	/**
	 * Returns the allowed methods.
	 */
	public function getAllowedMethods(): array
	{
		return $this->allowedMethods;
	}
}
