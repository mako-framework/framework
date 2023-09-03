<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\authorization\traits;

use mako\gatekeeper\authorization\AuthorizerInterface;

/**
 * Authorizable trait.
 */
trait AuthorizableTrait
{
	/**
	 * Authorizer.
	 */
	protected AuthorizerInterface $authorizer;

	/**
	 * Sets the authorizer.
	 */
	public function setAuthorizer(AuthorizerInterface $authorizer): void
	{
		$this->authorizer = $authorizer;
	}

	/**
	 * Returns TRUE if allowed to perform the action on the entity and FALSE if not.
	 */
	public function can(string $action, object|string $entity, mixed ...$parameters): bool
	{
		return $this->authorizer->can($this, $action, $entity, ...$parameters);
	}
}
