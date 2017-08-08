<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\entities\user;

/**
 * User entity interface.
 *
 * @author Frederic G. Østby
 */
interface UserEntityInterface
{
	/**
	 * Returns the user id.
	 *
	 * @return mixed
	 */
	public function getId();

	/**
	 * Returns the user username.
	 *
	 * @return string
	 */
	public function getUsername(): string;
}
