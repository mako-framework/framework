<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\authorization;

/**
 * Authorizable interface.
 */
interface AuthorizableInterface
{
	/**
	 * Sets the authorizer.
	 */
	public function setAuthorizer(AuthorizerInterface $authorizer): void;

	/**
	 * Returns TRUE if allowed to perform the action on the entity and FALSE if not.
	 */
	public function can(string $action, object|string $entity, mixed ...$parameters): bool;
}
