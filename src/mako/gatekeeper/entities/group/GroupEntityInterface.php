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
	 *
	 * @return mixed
	 */
	public function getId();

	/**
	 * Returns the group name.
	 *
	 * @return string
	 */
	public function getName(): string;
}
