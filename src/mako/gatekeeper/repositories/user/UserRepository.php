<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\repositories\user;

use InvalidArgumentException;
use mako\gatekeeper\authorization\AuthorizableInterface;
use mako\gatekeeper\authorization\AuthorizerInterface;

use function in_array;
use function vsprintf;

/**
 * User repository.
 *
 * @author Frederic G. Østby
 *
 * @method \mako\gatekeeper\entities\user\User      createUser(array $properties = [])
 * @method \mako\gatekeeper\entities\user\User|bool getByIdentifier($identifier)
 */
class UserRepository implements UserRepositoryInterface
{
	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Authorizer.
	 *
	 * @var \mako\gatekeeper\authorization\AuthorizerInterface|null
	 */
	protected $authorizer;

	/**
	 * User identifier.
	 *
	 * @var string
	 */
	protected $identifier = 'email';

	/**
	 * Constructor.
	 *
	 * @param string                                                  $model      Model name
	 * @param \mako\gatekeeper\authorization\AuthorizerInterface|null $authorizer Authorizer
	 */
	public function __construct(string $model, ?AuthorizerInterface $authorizer = null)
	{
		$this->model = $model;

		$this->authorizer = $authorizer;
	}

	/**
	 * Returns a model instance.
	 *
	 * @return \mako\database\midgard\ORM
	 */
	protected function getModel()
	{
		$model = $this->model;

		return new $model;
	}

	/**
	 * Sets the user identifier.
	 *
	 * @param  string                    $identifier User identifier
	 * @throws \InvalidArgumentException
	 */
	public function setIdentifier(string $identifier): void
	{
		if(!in_array($identifier, ['email', 'username', 'id']))
		{
			throw new InvalidArgumentException(vsprintf('Invalid identifier [ %s ].', [$identifier]));
		}

		$this->identifier = $identifier;
	}

	/**
	 * Sets the authorizer.
	 *
	 * @param  \mako\gatekeeper\entities\user\User|bool $user User
	 * @return \mako\gatekeeper\entities\user\User|bool
	 */
	protected function setAuthorizer($user)
	{
		if($user !== false && $this->authorizer !== null && $user instanceof AuthorizableInterface)
		{
			$user->setAuthorizer($this->authorizer);
		}

		return $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createUser(array $properties = [])
	{
		$user = $this->getModel();

		foreach($properties as $property => $value)
		{
			$user->$property = $value;
		}

		$user->generateAccessToken();

		$user->generateActionToken();

		$user->save();

		return $this->setAuthorizer($user);
	}

	/**
	 * Fetches a user by its action token.
	 *
	 * @param  string                                   $token Action token
	 * @return \mako\gatekeeper\entities\user\User|bool
	 */
	public function getByActionToken(string $token)
	{
		return $this->setAuthorizer($this->getModel()->where('action_token', '=', $token)->first());
	}

	/**
	 * Fetches a user by its access token.
	 *
	 * @param  string                                   $token Access token
	 * @return \mako\gatekeeper\entities\user\User|bool
	 */
	public function getByAccessToken(string $token)
	{
		return $this->setAuthorizer($this->getModel()->where('access_token', '=', $token)->first());
	}

	/**
	 * Fetches a user by its email address.
	 *
	 * @param  string                                   $email Email address
	 * @return \mako\gatekeeper\entities\user\User|bool
	 */
	public function getByEmail(string $email)
	{
		return $this->setAuthorizer($this->getModel()->where('email', '=', $email)->first());
	}

	/**
	 * Fetches a user by its username.
	 *
	 * @param  string                                   $username Username
	 * @return \mako\gatekeeper\entities\user\User|bool
	 */
	public function getByUsername(string $username)
	{
		return $this->setAuthorizer($this->getModel()->where('username', '=', $username)->first());
	}

	/**
	 * Fetches a user by its id.
	 *
	 * @param  int                                      $id User id
	 * @return \mako\gatekeeper\entities\user\User|bool
	 */
	public function getById(int $id)
	{
		return $this->setAuthorizer($this->getModel()->where('id', '=', $id)->first());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByIdentifier($identifier)
	{
		switch($this->identifier)
		{
			case 'email':
				return $this->getByEmail($identifier);
			case 'username':
				return $this->getByUsername($identifier);
			case 'id':
				return $this->getById($identifier);
		}
	}
}
