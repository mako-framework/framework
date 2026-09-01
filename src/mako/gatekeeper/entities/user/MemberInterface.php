<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\entities\user;

/**
 * Member interface.
 */
interface MemberInterface
{
	/**
	 * Returns true if a user is a member of the group(s) and false if not.
	 */
	public function isMemberOf(array|int|string $group): bool;
}
