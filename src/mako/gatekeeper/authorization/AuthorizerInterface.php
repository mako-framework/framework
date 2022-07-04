<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\authorization;

use mako\gatekeeper\entities\user\UserEntityInterface;

/**
 * Authorizer interface.
 */
interface AuthorizerInterface
{
	/**
	 * Registers an authorization policy.
	 *
	 * @param string $entityClass Entity class
	 * @param string $policyClass Policy class
	 */
	public function registerPolicy(string $entityClass, string $policyClass): void;

	/**
	 * Returns TRUE if the user is allowed to perform the action on the entity.
	 *
	 * @param  \mako\gatekeeper\entities\user\UserEntityInterface|null $user          User entity
	 * @param  string                                                  $action        Action
	 * @param  object|string                                           $entity        Entity instance or class name
	 * @param  mixed                                                   ...$parameters Additional parameters
	 * @return bool
	 */
	public function can(?UserEntityInterface $user, string $action, $entity, mixed ...$parameters): bool;
}
