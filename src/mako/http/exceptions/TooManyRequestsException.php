<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use DateTimeInterface;
use mako\chrono\TimeImmutable;
use mako\http\response\Status;
use Override;
use Throwable;

/**
 * Too many requests exception.
 */
class TooManyRequestsException extends HttpStatusException implements ProvidesHeadersInterface
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected string $defaultMessage = 'You have made too many requests to the server.';

	/**
	 * Constructor.
	 */
	public function __construct(
		string $message = '',
		?Throwable $previous = null,
		protected ?DateTimeInterface $retryAfter = null
	) {
		parent::__construct(Status::TooManyRequests, $message, $previous);
	}

	/**
	 * Returns the retry after time or NULL if one isn't set.
	 */
	public function getRetryAfter(): ?DateTimeInterface
	{
		return $this->retryAfter;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getHeaders(): array
	{
		if ($this->retryAfter !== null) {
			return ['Retry-After' => TimeImmutable::createFromInterface($this->retryAfter)->toRfc7231String()];
		}

		return [];
	}
}
