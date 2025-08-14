<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\entities\group;

use mako\database\midgard\ORM;
use mako\database\midgard\relations\ManyToMany;
use mako\database\midgard\traits\TimestampedTrait;
use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\exceptions\GatekeeperException;
use Override;

/**
 * Group.
 *
 * @method   int                              getId()
 * @property int                              $id
 * @property \mako\chrono\Time                $created_at
 * @property \mako\chrono\Time                $updated_at
 * @property string                           $name
 * @property \mako\database\midgard\ResultSet $users
 */
class Group extends ORM implements GroupEntityInterface
{
	use TimestampedTrait;

	/**
	 * Table name.
	 */
	protected string $tableName = 'groups';

	/**
	 * Group users.
	 */
	public function users(): ManyToMany
	{
		return $this->manyToMany(User::class);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getId(): mixed
	{
		return $this->id;
	}

	/**
	 * Sets the group name.
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Adds a user to the group.
	 */
	public function addUser(User $user): bool
	{
		if (!$this->isPersisted) {
			throw new GatekeeperException('You can only add a user to a group that exist in the database.');
		}

		if (!$user->isPersisted()) {
			throw new GatekeeperException('You can only add a user that exist in the database to a group.');
		}

		return $this->users()->link($user);
	}

	/**
	 * Removes a user from the group.
	 */
	public function removeUser(User $user): bool
	{
		if (!$this->isPersisted) {
			throw new GatekeeperException('You can only remove a user from a group that exist in the database.');
		}

		if (!$user->isPersisted()) {
			throw new GatekeeperException('You can only remove a user that exist in the database from a group.');
		}

		return $this->users()->unlink($user);
	}

	/**
	 * Returns TRUE if a user is a member of the group and FALSE if not.
	 */
	public function isMember(User $user)
	{
		if (!$this->isPersisted) {
			throw new GatekeeperException('You can only check if a user is a member of a group that exist in the database.');
		}

		if (!$user->isPersisted()) {
			throw new GatekeeperException('You can only check if a user that exist in the database is a member of a group.');
		}

		return $this->users()->where($user->getPrimaryKey(), '=', $user->getPrimaryKeyValue())->count() > 0;
	}
}
