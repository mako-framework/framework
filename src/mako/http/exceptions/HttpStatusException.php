<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use mako\http\response\StatusInterface;
use Throwable;

/**
 * HTTP status exception.
 */
class HttpStatusException extends HttpException
{
	/**
	 * HTTP status.
	 */
	protected StatusInterface $status;

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
	public function __construct(StatusInterface $status, string $message = '', ?Throwable $previous = null)
	{
		$this->status = $status;

		parent::__construct($message ?: $this->defaultMessage, $this->status->getCode(), $previous);
	}

	/**
	 * Returns the HTTP status.
	 */
	public function getStatus(): StatusInterface
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
