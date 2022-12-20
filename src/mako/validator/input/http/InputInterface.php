<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input\http;

use mako\validator\input\InputInterface as BaseInputInterface;

/**
 * HTTP input interface.
 */
interface InputInterface extends BaseInputInterface
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
