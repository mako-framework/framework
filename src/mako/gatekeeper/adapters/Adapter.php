<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\adapters;

use mako\gatekeeper\entities\group\Group;
use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\entities\user\UserEntityInterface;
use mako\gatekeeper\repositories\group\GroupRepository;
use mako\gatekeeper\repositories\group\GroupRepositoryInterface;
use mako\gatekeeper\repositories\user\UserRepository;
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
	 */
	protected UserRepository|null $userRepository = null;

	/**
	 * Group repository.
	 */
	protected GroupRepository|null $groupRepository = null;

	/**
	 * User entity.
	 */
	protected User|null $user = null;

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
	public function getUserRepository(): ?UserRepositoryInterface
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
	public function getGroupRepository(): ?GroupRepositoryInterface
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
