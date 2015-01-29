<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\providers;

/**
 * Group provider interface.
 *
 * @author  Frederic G. Østby
 */

interface GroupProviderInterface
{
	/**
	 * Creates and returns a group.
	 *
	 * @access  public
	 * @param   string                           $name  Group name
	 * @return  \mako\auth\group\GroupInterface
	 */

	public function createGroup($name);

	/**
	 * Fetches a group by its name.
	 *
	 * @access  public
	 * @param   string                                   $name  Group name
	 * @return  \mako\auth\group\GroupInterface|boolean
	 */

	public function getByName($name);

	/**
	 * Fetches a group by its id.
	 *
	 * @access  public
	 * @param   int                                      $id  Group id
	 * @return  \mako\auth\group\GroupInterface|boolean
	 */

	public function getById($id);
}