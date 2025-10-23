<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use mako\http\response\Status;
use Override;
use Throwable;

/**
 * Too many requests exception.
 */
class TooManyRequestsException extends HttpStatusException implements ProvidesHeadersInterface
{
	/**
	 * RFC 7231 date format.
	 */
	protected const string RFC_7231_DATE = 'D, d M Y H:i:s \G\M\T';

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
		protected ?DateTimeInterface $retryAfter = null
	) {
		parent::__construct(Status::TOO_MANY_REQUESTS, $message, $previous);
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
			$retryAfter = $this->retryAfter;

			// Ensure that the retry-after header is a UTC date

			if ($retryAfter->getTimezone()->getName() !== 'UTC' && ($retryAfter instanceof DateTime || $retryAfter instanceof DateTimeImmutable)) {
				$retryAfter = $retryAfter->setTimezone(new DateTimeZone('UTC'));
			}

			return ['Retry-After' => $retryAfter->format(static::RFC_7231_DATE)];
		}

		return [];
	}
}
