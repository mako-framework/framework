<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response;

use InvalidArgumentException;
use mako\http\response\traits\StatusTrait;
use Override;

/**
 * Custom status.
 */
final class CustomStatus implements StatusInterface
{
	use StatusTrait;

	public int $value { get => $this->statusCode; }

	/**
	 * Constructor.
	 */
	public function __construct(
		protected readonly int $statusCode,
		protected readonly string $statusText
	) {
		if ($statusCode < 100 || $statusCode > 599) {
			throw new InvalidArgumentException('The status code must be between 100 and 599.');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getMessage(): string
	{
		return $this->statusText;
	}
}
