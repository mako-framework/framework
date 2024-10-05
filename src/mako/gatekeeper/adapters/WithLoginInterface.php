<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\adapters;

use mako\gatekeeper\LoginStatus;
use SensitiveParameter;

interface WithLoginInterface
{
	/**
	 * Logs the user in.
	 */
	public function login(int|string $identifier, #[SensitiveParameter] string $password): LoginStatus;

	/**
	 * Logs the user out.
	 */
	public function logout(): void;
}
