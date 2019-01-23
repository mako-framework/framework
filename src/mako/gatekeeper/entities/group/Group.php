<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\entities\group;

use LogicException;
use mako\database\midgard\ORM;
use mako\database\midgard\relations\ManyToMany;
use mako\database\midgard\traits\TimestampedTrait;
use mako\gatekeeper\entities\user\User;

/**
 * Group.
 *
 * @author Frederic G. Østby
 *
 * @method   int                              getId()
 * @property int                              $id
 * @property \mako\utility\Time               $created_at
 * @property \mako\utility\Time               $updated_at
 * @property string                           $name
 * @property \mako\database\midgard\ResultSet $users
 */
class Group extends ORM implements GroupEntityInterface
{
	use TimestampedTrait;

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $tableName = 'groups';

	/**
	 * Group users.
	 *
	 * @return \mako\database\midgard\relations\ManyToMany
	 */
	public function users(): ManyToMany
	{
		return $this->manyToMany(User::class);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Sets the group name.
	 *
	 * @param string $name Group name
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Adds a user to the group.
	 *
	 * @param  \mako\gatekeeper\entities\user\User $user User
	 * @throws \LogicException
	 * @return bool
	 */
	public function addUser(User $user): bool
	{
		if(!$this->isPersisted)
		{
			throw new LogicException('You can only add a user to a group that exist in the database.');
		}

		if(!$user->isPersisted())
		{
			throw new LogicException('You can only add a user that exist in the database to a group.');
		}

		return $this->users()->link($user);
	}

	/**
	 * Removes a user from the group.
	 *
	 * @param  \mako\gatekeeper\entities\user\User $user User
	 * @throws \LogicException
	 * @return bool
	 */
	public function removeUser(User $user): bool
	{
		if(!$this->isPersisted)
		{
			throw new LogicException('You can only remove a user from a group that exist in the database.');
		}

		if(!$user->isPersisted())
		{
			throw new LogicException('You can only remove a user that exist in the database from a group.');
		}

		return $this->users()->unlink($user);
	}

	/**
	 * Returns TRUE if a user is a member of the group and FALSE if not.
	 *
	 * @param  \mako\gatekeeper\entities\user\User $user User
	 * @throws \LogicException
	 * @return bool
	 */
	public function isMember(User $user)
	{
		if(!$this->isPersisted)
		{
			throw new LogicException('You can only check if a user is a member of a group that exist in the database.');
		}

		if(!$user->isPersisted())
		{
			throw new LogicException('You can only check if a user that exist in the database is a member of a group.');
		}

		return $this->users()->where($user->getPrimaryKey(), '=', $user->getPrimaryKeyValue())->count() > 0;
	}
}
