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
	case OK = 1;
	case BANNED = 2;
	case NOT_ACTIVATED = 3;
	case INVALID_CREDENTIALS = 4;
	case LOCKED = 5;

	/**
	 * Returns the status code.
	 */
	public function getCode(): int
	{
		return $this->value;
	}

	/**
	 * Returns TRUE if OK and FALSE otherwise.
	 */
	public function toBool(): bool
	{
		return $this === self::OK;
	}
}
