<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\group;

/**
 * Member interface.
 *
 * @author  Frederic G. Østby
 */

interface MemberInterface
{
	/**
	 * Returns TRUE if a user is a member of the group(s) and FALSE if not.
	 *
	 * @access  public
	 * @param   string|int|array  $group  Group name, group id or an array of group names or group ids
	 * @return  boolean
	 */

	public function isMemberOf($group);
}