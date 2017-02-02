<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\entities\group;

/**
 * Group entity interface.
 *
 * @author Frederic G. Østby
 */
interface GroupEntityInterface
{
	/**
	 * Returns the group id.
	 *
	 * @access public
	 * @return mixed
	 */
	public function getId();

	/**
	 * Returns the group name.
	 *
	 * @access public
	 * @return string
	 */
	public function getName(): string;
}
