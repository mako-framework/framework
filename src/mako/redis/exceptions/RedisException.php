<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis\exceptions;

use RuntimeException;
use Throwable;

/**
 * Redis exception.
 */
class RedisException extends RuntimeException
{
	/**
	 * Constructor.
	 */
	public function __construct(
		string $message = '',
		int $code = 0,
		?Throwable $previous = null,
		protected ?array $streamMetadata = null
	) {
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Returns the stream metadata.
	 */
	public function getStreamMetadata(): ?array
	{
		return $this->streamMetadata;
	}
}
