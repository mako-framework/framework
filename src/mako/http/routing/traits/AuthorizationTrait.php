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
 */
trait AuthorizationTrait
{
	/**
	 * Throws a ForbiddenException if the user is not allowed to perform the action on the entity.
	 *
	 * @param  string                                   $action Action
	 * @param  object|string                            $entity Entity instance or class name
	 * @throws \mako\http\exceptions\ForbiddenException
	 */
	protected function authorize(string $action, $entity): void
	{
		if($this->authorizer->can($this->gatekeeper->getUser(), $action, $entity) === false)
		{
			throw new ForbiddenException;
		}
	}
}
