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
	 * Headers.
	 */
	protected $headers = [];

	/**
	 * {@inheritDoc}
	 */
	protected string $defaultMessage = 'You have made too many requests to the server.';

	/**
	 * Constructor.
	 */
	public function __construct(?DateTimeInterface $retryAfter = null, string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(Status::TOO_MANY_REQUESTS, $message, $previous);

		if ($retryAfter !== null) {
			$this->headers['Retry-After'] = $retryAfter->format(DateTimeInterface::RFC7231);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}
}
