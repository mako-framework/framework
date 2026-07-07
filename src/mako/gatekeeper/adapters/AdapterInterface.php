<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\adapters;

use mako\gatekeeper\entities\user\UserEntityInterface;
use mako\gatekeeper\repositories\user\UserRepositoryInterface;

/**
 * Adapter interface.
 *
 * @template TRepository of UserRepositoryInterface
 * @template TUser of UserEntityInterface
 */
interface AdapterInterface
{
	/**
	 * Returns the adapter name.
	 */
	public function getName(): string;

	/**
	 * Sets the user repository.
	 *
	 * @param TRepository $userRepository
	 */
	public function setUserRepository(UserRepositoryInterface $userRepository): void;

	/**
	 * Returns the user repository.
	 *
	 * @return ?TRepository
	 */
	public function getUserRepository(): ?UserRepositoryInterface;

	/**
	 * Sets the active user.
	 *
	 * @param ?TUser $user
	 */
	public function setUser(?UserEntityInterface $user): void;

	/**
	 * Returns the active user or NULL if there isn't one.
	 */
	public function getUser(): ?UserEntityInterface;

	/**
	 * Returns TRUE if we don't have an active user and FALSE if we do.
	 */
	public function isGuest(): bool;

	/**
	 * Returns TRUE if we have an active user and FALSE if we don't.
	 */
	public function isLoggedIn(): bool;
}
