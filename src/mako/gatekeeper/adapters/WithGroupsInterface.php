<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\adapters;

use mako\gatekeeper\repositories\group\GroupRepositoryInterface;

/**
 * With groups interface.
 *
 * @author Frederic G. Østby
 */
interface WithGroupsInterface
{
	/**
	 * Sets the group repository.
	 *
	 * @param \mako\gatekeeper\repositories\group\GroupRepositoryInterface $groupRepository Group repository
	 */
	public function setGroupRepository(GroupRepositoryInterface $groupRepository);

	/**
	 * Returns the group repository.
	 *
	 * @return \mako\gatekeeper\repositories\group\GroupRepositoryInterface
	 */
	public function getGroupRepository();
}
