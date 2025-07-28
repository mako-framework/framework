<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\repositories\user;

use mako\database\types\SensitiveString;
use mako\gatekeeper\authorization\AuthorizableInterface;
use mako\gatekeeper\authorization\AuthorizerInterface;
use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\exceptions\GatekeeperException;
use SensitiveParameter;

use function in_array;
use function sprintf;

/**
 * User repository.
 *
 * @method \mako\gatekeeper\entities\user\User      createUser(array $properties = [])
 * @method \mako\gatekeeper\entities\user\User|null getByIdentifier(int|string $identifier)
 */
class UserRepository implements UserRepositoryInterface
{
	/**
	 * User identifier.
	 */
	protected string $identifier = 'email';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $model,
		protected ?AuthorizerInterface $authorizer = null
	) {
	}

	/**
	 * Returns a model instance.
	 */
	protected function getModel(): User
	{
		return new $this->model;
	}

	/**
	 * Sets the user identifier.
	 */
	public function setIdentifier(string $identifier): void
	{
		if (!in_array($identifier, ['email', 'username', 'id'])) {
			throw new GatekeeperException(sprintf('Invalid identifier [ %s ].', $identifier));
		}

		$this->identifier = $identifier;
	}

	/**
	 * Sets the authorizer.
	 */
	protected function setAuthorizer(?User $user): ?User
	{
		if ($user !== null && $this->authorizer !== null && $user instanceof AuthorizableInterface) {
			$user->setAuthorizer($this->authorizer);
		}

		return $user;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createUser(array $properties = []): User
	{
		$user = $this->getModel();

		foreach ($properties as $property => $value) {
			$user->{$property} = $value;
		}

		$user->generateAccessToken();

		$user->generateActionToken();

		$user->save();

		return $this->setAuthorizer($user);
	}

	/**
	 * Fetches a user by its action token.
	 */
	public function getByActionToken(#[SensitiveParameter] string $token): ?User
	{
		return $this->setAuthorizer($this->getModel()->where('action_token', '=', new SensitiveString($token))->first());
	}

	/**
	 * Fetches a user by its access token.
	 */
	public function getByAccessToken(#[SensitiveParameter] string $token): ?User
	{
		return $this->setAuthorizer($this->getModel()->where('access_token', '=', new SensitiveString($token))->first());
	}

	/**
	 * Fetches a user by its email address.
	 */
	public function getByEmail(string $email): ?User
	{
		return $this->setAuthorizer($this->getModel()->where('email', '=', $email)->first());
	}

	/**
	 * Fetches a user by its username.
	 */
	public function getByUsername(string $username): ?User
	{
		return $this->setAuthorizer($this->getModel()->where('username', '=', $username)->first());
	}

	/**
	 * Fetches a user by its id.
	 */
	public function getById(int $id): ?User
	{
		return $this->setAuthorizer($this->getModel()->where('id', '=', $id)->first());
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return User|null
	 */
	public function getByIdentifier(int|string $identifier): ?User
	{
		return match ($this->identifier) {
			'email'    => $this->getByEmail($identifier),
			'username' => $this->getByUsername($identifier),
			'id'       => $this->getById($identifier),
			default    => throw new GatekeeperException(sprintf('Invalid identifier [ %s ].', $identifier)),
		};
	}
}
