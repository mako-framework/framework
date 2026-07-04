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
 * Length required rxception.
 */
class LengthRequiredException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected string $defaultMessage = 'A Content-Length header is required for this request.';

	/**
	 * Constructor.
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(Status::LengthRequired, $message, $previous);
	}
}
