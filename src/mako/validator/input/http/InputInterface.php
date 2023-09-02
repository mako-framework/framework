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
	 */
	public function shouldRedirect(): bool;

	/**
	 * Returns the redirect URL.
	 */
	public function getRedirectUrl(): string;

	/**
	 * Should the old input be included?
	 */
	public function shouldIncludeOldInput(): bool;

	/**
	 * Returns the old input.
	 */
	public function getOldInput(): array;
}
