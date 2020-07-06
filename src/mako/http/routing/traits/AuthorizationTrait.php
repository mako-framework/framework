<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\traits;

use mako\http\exceptions\ForbiddenException;

/**
 * Authorization trait.
 *
 * @author Frederic G. Østby
 *
 * @property \mako\gatekeeper\authorization\AuthorizerInterface $authorizer
 */
trait AuthorizationTrait
{
	/**
	 * Throws a ForbiddenException if the user is not allowed to perform the action on the entity.
	 *
	 * @param string        $action        Action
	 * @param object|string $entity        Entity instance or class name
	 * @param mixed         ...$parameters Additional parameters
	 */
	protected function authorize(string $action, $entity, ...$parameters): void
	{
		if($this->authorizer->can($this->gatekeeper->getUser(), $action, $entity, ...$parameters) === false)
		{
			throw new ForbiddenException;
		}
	}
}
