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
 * Unavailable for legal reasons exception.
 */
class UnavailableForLegalReasonsException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected string $defaultMessage = 'The requested resource is unavailable for legal reasons.';

	/**
	 * Constructor.
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(Status::UnavailableForLegalReasons, $message, $previous);
	}
}
