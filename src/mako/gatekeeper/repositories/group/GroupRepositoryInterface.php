<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\repositories\group;

use mako\gatekeeper\entities\group\GroupEntityInterface;

/**
 * Group repository interface.
 */
interface GroupRepositoryInterface
{
	/**
	 * Creates and returns a group.
	 */
	public function createGroup(array $properties = []): GroupEntityInterface;

	/**
	 * Gets a group by its identifier.
	 */
	public function getByIdentifier(int|string $identifier): ?GroupEntityInterface;
}
