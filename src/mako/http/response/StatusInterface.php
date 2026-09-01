<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response;

/**
 * Status interface.
 */
interface StatusInterface
{
	public int $value { get; }

	/**
	 * Returns the status code.
	 */
	public function getCode(): int;

	/**
	 * Returns the status message.
	 */
	public function getMessage(): string;

	/**
	 * Returns true if the status is informational and false if not.
	 */
	public function isInformational(): bool;

	/**
	 * Returns true if the status is successful and false if not.
	 */
	public function isSuccessful(): bool;

	/**
	 * Returns true if the status is a redirection and false if not.
	 */
	public function isRedirection(): bool;

	/**
	 * Returns true if the status is a client error and false if not.
	 */
	public function isClientError(): bool;

	/**
	 * Returns true if the status is a server error and false if not.
	 */
	public function isServerError(): bool;
}
