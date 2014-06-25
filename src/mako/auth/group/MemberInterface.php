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
	public function isMemberOf($group);
}