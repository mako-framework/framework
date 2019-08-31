<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\authorization;

/**
 * Authorizable interface.
 *
 * @author Frederic G. Østby
 */
interface AuthorizableInterface
{
	/**
	 * Sets the authorizer.
	 *
	 * @param \mako\gatekeeper\authorization\AuthorizerInterface $authorizer Authorizer
	 */
	public function setAuthorizer(AuthorizerInterface $authorizer): void;

	/**
	 * Returns TRUE if allowed to perform the action on the entity and FALSE if not.
	 *
	 * @param  string        $action        Action
	 * @param  object|string $entity        Entity
	 * @param  mixed         ...$parameters Additional parameters
	 * @return bool
	 */
	public function can(string $action, $entity, ...$parameters): bool;
}
