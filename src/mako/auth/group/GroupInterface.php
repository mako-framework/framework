<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\group;

use \mako\auth\user\UserInterface;

/**
 * Group interface.
 *
 * @author  Frederic G. Østby
 */

interface GroupInterface
{
	public function getId();
	public function setName($name);
	public function getName();
	public function addUser(UserInterface $user);
	public function removeUser(UserInterface $user);
	public function isMember(UserInterface $user);
	public function save();
	public function delete();
}