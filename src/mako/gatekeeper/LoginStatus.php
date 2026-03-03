<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper;

use Deprecated;

/**
 * Login status codes.
 */
enum LoginStatus: int
{
	/* Start compatibility */
	#[Deprecated('use LoginStatus::Ok instead', 'Mako 12.2.0')]
	public const OK = self::Ok;
	#[Deprecated('use LoginStatus::Banned instead', 'Mako 12.2.0')]
	public const BANNED = self::Banned;
	#[Deprecated('use LoginStatus::NotActivated instead', 'Mako 12.2.0')]
	public const NOT_ACTIVATED = self::NotActivated;
	#[Deprecated('use LoginStatus::InvalidCredentials instead', 'Mako 12.2.0')]
	public const INVALID_CREDENTIALS = self::InvalidCredentials;
	#[Deprecated('use LoginStatus::Locked instead', 'Mako 12.2.0')]
	public const LOCKED = self::Locked;
	/* End compatibility */

	case Ok = 1;
	case Banned = 2;
	case NotActivated = 3;
	case InvalidCredentials = 4;
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
