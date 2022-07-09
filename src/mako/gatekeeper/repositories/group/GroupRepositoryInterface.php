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
	 *
	 * @param  array                                                $properties Group properties
	 * @return \mako\gatekeeper\entities\group\GroupEntityInterface
	 */
	public function createGroup(array $properties = []): GroupEntityInterface;

	/**
	 * Gets a group by its identifier.
	 *
	 * @param  int|string                                                $identifier Group identifier
	 * @return \mako\gatekeeper\entities\group\GroupEntityInterface|null
	 */
	public function getByIdentifier(int|string $identifier): ?GroupEntityInterface;
}
