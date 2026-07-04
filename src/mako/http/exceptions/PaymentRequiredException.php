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
 * Payment required exception.
 */
class PaymentRequiredException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected string $defaultMessage = 'Payment is required to access the requested resource.';

	/**
	 * Constructor.
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(Status::PaymentRequired, $message, $previous);
	}
}
