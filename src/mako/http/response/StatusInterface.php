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
	 * Returns TRUE if the status is informational and FALSE if not.
	 */
	public function isInformational(): bool;

	/**
	 * Returns TRUE if the status is successful and FALSE if not.
	 */
	public function isSuccessful(): bool;

	/**
	 * Returns TRUE if the status is a redirection and FALSE if not.
	 */
	public function isRedirection(): bool;

	/**
	 * Returns TRUE if the status is a client error and FALSE if not.
	 */
	public function isClientError(): bool;

	/**
	 * Returns TRUE if the status is a server error and FALSE if not.
	 */
	public function isServerError(): bool;
}
