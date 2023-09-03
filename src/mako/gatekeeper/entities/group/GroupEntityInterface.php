<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\entities\group;

/**
 * Group entity interface.
 */
interface GroupEntityInterface
{
	/**
	 * Returns the group id.
	 */
	public function getId(): mixed;

	/**
	 * Returns the group name.
	 */
	public function getName(): string;
}
