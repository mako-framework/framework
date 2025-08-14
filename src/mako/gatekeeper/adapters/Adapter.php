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
use Override;
use SensitiveParameter;

/**
 * Base adapter.
 *
 * @method \mako\gatekeeper\repositories\group\GroupRepository getGroupRepository()
 * @method \mako\gatekeeper\repositories\user\UserRepository   getUserRepository()
 */
abstract class Adapter implements AdapterInterface, WithGroupsInterface, WithLoginInterface
{
	/**
	 * User repository.
	 */
	protected ?UserRepository $userRepository = null;

	/**
	 * Group repository.
	 */
	protected ?GroupRepository $groupRepository = null;

	/**
	 * User entity.
	 */
	protected ?User $user = null;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function setUserRepository(UserRepositoryInterface $userRepository): void
	{
		$this->userRepository = $userRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getUserRepository(): ?UserRepositoryInterface
	{
		return $this->userRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function setGroupRepository(GroupRepositoryInterface $groupRepository): void
	{
		$this->groupRepository = $groupRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getGroupRepository(): ?GroupRepositoryInterface
	{
		return $this->groupRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function setUser(?UserEntityInterface $user): void
	{
		$this->user = $user;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function isGuest(): bool
	{
		return $this->getUser() === null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function isLoggedIn(): bool
	{
		return $this->getUser() !== null;
	}

	/**
	 * Creates a new user and returns the user object.
	 */
	public function createUser(string $email, string $username, #[SensitiveParameter] string $password, bool $activate = false, array $properties = []): User
	{
		$properties = [
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
		$properties = [
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

		if ($user === null) {
			return false;
		}

		$user->activate();

		$user->generateActionToken();

		$user->save();

		return true;
	}
}
