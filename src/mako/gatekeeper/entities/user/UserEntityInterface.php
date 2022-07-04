<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\entities\user;

/**
 * User entity interface.
 */
interface UserEntityInterface
{
	/**
	 * Returns the user id.
	 *
	 * @return mixed
	 */
	public function getId(): mixed;

	/**
	 * Returns the user username.
	 *
	 * @return string
	 */
	public function getUsername(): string;
}
