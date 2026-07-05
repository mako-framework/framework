<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper;

/**
 * Login status codes.
 */
enum LoginStatus: int
{
	/**
	 * The login was successful.
	 */
	case Ok = 1;

	/**
	 * The account is banned.
	 */
	case Banned = 2;

	/**
	 * The account is not activated.
	 */
	case NotActivated = 3;

	/**
	 * Invalid credentials were provided.
	 */
	case InvalidCredentials = 4;

	/**
	 * The account is locked.
	 */
	case Locked = 5;

	/**
	 * Returns the status code.
	 */
	public function getCode(): int
	{
		return $this->value;
	}

	/**
	 * Returns TRUE if Ok and FALSE otherwise.
	 */
	public function toBool(): bool
	{
		return $this === self::Ok;
	}
}
