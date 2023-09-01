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
	 * Returns TRUE if a user is a member of the group(s) and FALSE if not.
	 */
	public function isMemberOf(array|int|string $group): bool;
}
