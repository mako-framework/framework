<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\repositories\group;

/**
 * Group repository interface.
 *
 * @author Frederic G. Østby
 */
interface GroupRepositoryInterface
{
	/**
	 * Creates and returns a group.
	 *
	 * @access public
	 * @param  array                                                $properties Group properties
	 * @return \mako\gatekeeper\entities\group\GroupEntityInterface
	 */
	public function createGroup(array $properties = []);

	/**
	 * Gets a group by its identifier.
	 *
	 * @access public
	 * @param  mixed                                                $identifier Group identifier
	 * @return \mako\gatekeeper\entities\group\GroupEntityInterface
	 */
	public function getByIdentifier($identifier);
}
