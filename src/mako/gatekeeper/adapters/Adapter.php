<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\adapters;

use mako\gatekeeper\entities\group\Group;
use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\entities\user\UserEntityInterface;
use mako\gatekeeper\repositories\group\GroupRepositoryInterface;
use mako\gatekeeper\repositories\user\UserRepositoryInterface;

/**
 * Base adapter.
 *
 * @method mako\gatekeeper\repositories\group\GroupRepository getGroupRepository()
 * @method mako\gatekeeper\repositories\user\UserRepository   getUserRepository()
 */
abstract class Adapter implements AdapterInterface, WithGroupsInterface
{
	/**
	 * User repository.
	 *
	 * @var \mako\gatekeeper\repositories\user\UserRepository
	 */
	protected $userRepository;

	/**
	 * Group repository.
	 *
	 * @var \mako\gatekeeper\repositories\group\GroupRepository
	 */
	protected $groupRepository;

	/**
	 * User entity.
	 *
	 * @var \mako\gatekeeper\entities\user\User|null
	 */
	protected $user;

	/**
	 * {@inheritDoc}
	 */
	public function setUserRepository(UserRepositoryInterface $userRepository): void
	{
		$this->userRepository = $userRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUserRepository()
	{
		return $this->userRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setGroupRepository(GroupRepositoryInterface $groupRepository): void
	{
		$this->groupRepository = $groupRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getGroupRepository()
	{
		return $this->groupRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUser(UserEntityInterface $user): void
	{
		$this->user = $user;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isGuest(): bool
	{
		return $this->getUser() === null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isLoggedIn(): bool
	{
		return $this->getUser() !== null;
	}

	/**
	 * Creates a new user and returns the user object.
	 *
	 * @param  string                              $email      Email address
	 * @param  string                              $username   Username
	 * @param  string                              $password   Password
	 * @param  bool                                $activate   Will activate the user if set to true
	 * @param  array                               $properties Additional user properties
	 * @return \mako\gatekeeper\entities\user\User
	 */
	public function createUser(string $email, string $username, string $password, bool $activate = false, array $properties = []): User
	{
		$properties =
		[
			'email'     => $email,
			'username'  => $username,
			'password'  => $password,
			'activated' => $activate ? 1 : 0,
		] + $properties;

		return $this->userRepository->createUser($properties);
	}

	/**
	 * Creates a new group and returns the group object.
	 *
	 * @param  string                                $name       Group name
	 * @param  array                                 $properties Additional group properties
	 * @return \mako\gatekeeper\entities\group\Group
	 */
	public function createGroup(string $name, array $properties = []): Group
	{
		$properties =
		[
			'name' => $name,
		] + $properties;

		return $this->groupRepository->createGroup($properties);
	}

	/**
	 * Activates a user based on the provided action token.
	 *
	 * @param  string $token Action token
	 * @return bool
	 */
	public function activateUser(string $token): bool
	{
		$user = $this->userRepository->getByActionToken($token);

		if($user === null)
		{
			return false;
		}

		$user->activate();

		$user->generateActionToken();

		$user->save();

		return true;
	}
}
