<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * HTTP status exception.
 */
class HttpStatusException extends HttpException
{
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
	public function __construct(int $code, string $message = '', ?Throwable $previous = null)
	{
		parent::__construct($message ?: $this->defaultMessage, $code, $previous);
	}

	/**
	 * Sets exception metadata.
	 *
	 * @return $this
	 */
	public function setMetadata(array $metadata)
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
