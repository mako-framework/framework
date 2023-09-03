<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\adapters;

use mako\gatekeeper\repositories\group\GroupRepositoryInterface;

/**
 * With groups interface.
 */
interface WithGroupsInterface
{
	/**
	 * Sets the group repository.
	 */
	public function setGroupRepository(GroupRepositoryInterface $groupRepository): void;

	/**
	 * Returns the group repository.
	 */
	public function getGroupRepository(): ?GroupRepositoryInterface;
}
