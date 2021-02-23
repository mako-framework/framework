<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use RuntimeException;
use Throwable;

/**
 * HTTP exception.
 */
class HttpException extends RuntimeException
{
	/**
	 * Default message.
	 *
	 * @var string|null
	 */
	protected $defaultMessage;

	/**
	 * Exception metadata.
	 *
	 * @var array
	 */
	protected $metadata = [];

	/**
	 * Constructor.
	 *
	 * @param int             $code     Exception code
	 * @param string          $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(int $code, string $message = '', ?Throwable $previous = null)
	{
		parent::__construct($message ?: (string) $this->defaultMessage, $code, $previous);
	}

	/**
	 * Sets exception metadata.
	 *
	 * @param  array $metadata Exception metadata
	 * @return $this
	 */
	public function setMetadata(array $metadata)
	{
		$this->metadata = $metadata;

		return $this;
	}

	/**
	 * Returns exception metadata.
	 *
	 * @return array
	 */
	public function getMetadata(): array
	{
		return $this->metadata;
	}
}
