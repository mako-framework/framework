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
 * Content too targe exception.
 */
class ContentTooLargeException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected string $defaultMessage = 'The request body exceeds the allowed size limit.';

	/**
	 * Constructor.
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(Status::ContentTooLarge, $message, $previous);
	}
}
