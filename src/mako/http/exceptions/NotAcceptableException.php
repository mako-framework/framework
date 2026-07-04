<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use mako\http\response\Status;
use Override;
use Throwable;

/**
 * Not acceptable exception.
 */
class NotAcceptableException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected string $defaultMessage = 'The requested resource is not available in an acceptable format.';

	/**
	 * Constructor.
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(Status::NotAcceptable, $message, $previous);
	}
}
