<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\providers;

use \mako\auth\user\UserInterface;

/**
 * Group provider interface.
 *
 * @author  Frederic G. Østby
 */

interface GroupProviderInterface
{
	public function createGroup($name);
	public function getByName($name);
	public function getById($id);
}