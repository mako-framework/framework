<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\group;

use LogicException;

use mako\auth\group\GroupInterface;
use mako\auth\user\UserInterface;
use mako\database\midgard\ORM;
use mako\database\midgard\traits\TimestampedTrait;

/**
 * Group.
 *
 * @author  Frederic G. Østby
 */

class Group extends ORM implements GroupInterface
{
	use TimestampedTrait;

	/**
	 * Table name.
	 *
	 * @var string
	 */

	protected $tableName = 'groups';

	/**
	 * {@inheritdoc}
	 */

	public function getId()
	{
		return $this->getPrimaryKeyValue();
	}

	/**
	 * {@inheritdoc}
	 */

	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * {@inheritdoc}
	 */

	public function getName()
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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