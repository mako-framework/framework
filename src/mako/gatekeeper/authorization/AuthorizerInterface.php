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
	 */
	public function registerPolicy(string $entityClass, string $policyClass): void;

	/**
	 * Returns TRUE if the user is allowed to perform the action on the entity.
	 */
	public function can(?UserEntityInterface $user, string $action, object|string $entity, mixed ...$parameters): bool;
}
