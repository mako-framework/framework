<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\group;

use \LogicException;

use \mako\auth\user\UserInterface;

/**
 * Group.
 *
 * @author  Frederic G. Østby
 */

class Group extends \mako\database\midgard\ORM implements \mako\auth\group\GroupInterface
{
	use \mako\database\midgard\traits\TimestampedTrait;

	/**
	 * Table name.
	 * 
	 * @var string
	 */

	protected $tableName = 'groups';

	/**
	 * Returns the user id.
	 * 
	 * @access  public
	 * @return  int|string
	 */

	public function getId()
	{
		return $this->getPrimaryKeyValue();
	}

	/**
	 * Sets the group name.
	 * 
	 * @access  public
	 * @param   string  $name  Group name
	 */

	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Returns the group name.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getName()
	{
		return $this->name;
	}

	/**
	 * Adds a user to the group.
	 * 
	 * @access  public
	 * @param   \mako\auth\user\UserInterface  $user  User intance
	 * @return  boolean
	 */

	public function addUser(UserInterface $user)
	{
		if(!$this->exists)
		{
			throw new LogicException(vsprintf("%s(): You can only add a user to a group that exist in the database.", [__METHOD__]));
		}

		return $this->users()->link($user);
	}

	/**
	 * Removes a user from the group.
	 * 
	 * @access  public
	 * @param   \mako\auth\user\UserInterface  $user  User intance
	 * @return  boolean
	 */

	public function removeUser(UserInterface $user)
	{
		if(!$this->exists)
		{
			throw new LogicException(vsprintf("%s(): You can only remove a user from a group that exist in the database.", [__METHOD__]));
		}

		return $this->users()->unlink($user);
	}

	/**
	 * Returns TRUE if a user is a member of the group and FALSE if not.
	 * 
	 * @access  public
	 * @param   \mako\auth\user\UserInterface  $user  User intance
	 * @return  boolean
	 */

	public function isMember(UserInterface $user)
	{
		if(!$this->exists)
		{
			throw new LogicException(vsprintf("%s(): You can only check if a user is a member of a group that exist in the database.", [__METHOD__]));
		}

		return $this->users()->where($user->getPrimaryKey(), '=', $user->getPrimaryKeyValue())->count() > 0;
	}

	/**
	 * Group users.
	 * 
	 * @access  public
	 * @return  \mako\database\midgard\relations\ManyToMany
	 */

	public function users()
	{
		return $this->manyToMany('mako\auth\user\User');
	}
}