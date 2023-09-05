<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use mako\http\response\Status;
use Throwable;

use function is_int;

/**
 * HTTP status exception.
 */
class HttpStatusException extends HttpException
{
	/**
	 * HTTP status.
	 */
	protected Status $status;

	/**
	 * Default message.
	 */
	protected string $defaultMessage = '';

	/**
	 * Exception metadata.
	 */
	protected array $metadata = [];

	/**
	 * Constructor.
	 */
	public function __construct(int|Status $status, string $message = '', ?Throwable $previous = null)
	{
		$this->status = is_int($status) ? Status::from($status) : $status;

		parent::__construct($message ?: $this->defaultMessage, $this->status->value, $previous);
	}

	/**
	 * Returns the HTTP status.
	 */
	public function getStatus(): Status
	{
		return $this->status;
	}

	/**
	 * Sets exception metadata.
	 *
	 * @return $this
	 */
	public function setMetadata(array $metadata): static
	{
		$this->metadata = $metadata;

		return $this;
	}

	/**
	 * Returns exception metadata.
	 */
	public function getMetadata(): array
	{
		return $this->metadata;
	}
}
