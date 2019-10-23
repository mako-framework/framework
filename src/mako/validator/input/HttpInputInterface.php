<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input;

/**
 * HTTP input interface.
 *
 * @author Frederic G. Østby
 */
interface HttpInputInterface extends InputInterface
{
	/**
	 * Should we redirect the client if possible?
	 *
	 * @return bool
	 */
	public function shouldRedirect(): bool;

	/**
	 * Returns the redirect URL.
	 *
	 * @return string
	 */
	public function getRedirectUrl(): string;

	/**
	 * Should the old input be included?
	 *
	 * @return bool
	 */
	public function shouldIncludeOldInput(): bool;

	/**
	 * Returns the old input.
	 *
	 * @return array
	 */
	public function getOldInput(): array;
}
