<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\repositories\user;

/**
 * User repository interface.
 *
 * @author Frederic G. Østby
 */
interface UserRepositoryInterface
{
	/**
	 * Creates and returns a user.
	 *
	 * @param  array                                              $properties User properties
	 * @return \mako\gatekeeper\entities\user\UserEntityInterface
	 */
	public function createUser(array $properties = []);

	/**
	 * Fetches a user by its identifier.
	 *
	 * @param  string|int                                              $identifier User identifier
	 * @return \mako\gatekeeper\entities\user\UserEntityInterface|null
	 */
	public function getByIdentifier($identifier);
}
