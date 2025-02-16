<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use DateTimeInterface;
use mako\http\response\Status;
use Throwable;

/**
 * Too many requests exception.
 */
class TooManyRequestsException extends HttpStatusException implements ProvidesHeadersInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected string $defaultMessage = 'You have made too many requests to the server.';

	/**
	 * Constructor.
	 */
	public function __construct(
		string $message = '',
		?Throwable $previous = null,
		protected ?DateTimeInterface $retryAfter = null)
	{
		parent::__construct(Status::TOO_MANY_REQUESTS, $message, $previous);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHeaders(): array
	{
		if ($this->retryAfter !== null) {
			return ['Retry-After' => $this->retryAfter->format(DateTimeInterface::RFC7231)];
		}

		return [];
	}
}
