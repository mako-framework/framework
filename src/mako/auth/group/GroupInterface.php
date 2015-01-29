<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\group;

use mako\auth\user\UserInterface;

/**
 * Group interface.
 *
 * @author  Frederic G. Østby
 */

interface GroupInterface
{
	/**
	 * Returns the group id.
	 *
	 * @access  public
	 * @return  int|string
	 */

	public function getId();

	/**
	 * Sets the group name.
	 *
	 * @access  public
	 * @param   string  $name  Group name
	 */

	public function setName($name);

	/**
	 * Returns the group name.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getName();

	/**
	 * Adds a user to the group.
	 *
	 * @access  public
	 * @param   \mako\auth\user\UserInterface  $user  User intance
	 * @return  boolean
	 */

	public function addUser(UserInterface $user);

	/**
	 * Removes a user from the group.
	 *
	 * @access  public
	 * @param   \mako\auth\user\UserInterface  $user  User intance
	 * @return  boolean
	 */

	public function removeUser(UserInterface $user);

	/**
	 * Returns TRUE if a user is a member of the group and FALSE if not.
	 *
	 * @access  public
	 * @param   \mako\auth\user\UserInterface  $user  User intance
	 * @return  boolean
	 */

	public function isMember(UserInterface $user);

	/**
	 * Saves the group.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function save();

	/**
	 * Deletes the group.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function delete();
}