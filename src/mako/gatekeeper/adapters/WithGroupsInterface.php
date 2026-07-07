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
 * @template TRepository of GroupRepositoryInterface
 */
interface WithGroupsInterface
{
	/**
	 * Sets the group repository.
	 *
	 * @param TRepository $groupRepository
	 */
	public function setGroupRepository(GroupRepositoryInterface $groupRepository): void;

	/**
	 * Returns the group repository.
	 *
	 * @return TRepository
	 */
	public function getGroupRepository(): ?GroupRepositoryInterface;
}
