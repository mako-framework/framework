<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\repositories\user;

use InvalidArgumentException;

use mako\gatekeeper\repositories\user\UserRepositoryInterface;

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
	 * User identifier.
	 *
	 * @var string
	 */
	protected $identifier = 'email';

	/**
	 * Constructor.
	 *
	 * @param string $model Model name
	 */
	public function __construct(string $model)
	{
		$this->model = $model;
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

		return $user;
	}

	/**
	 * Sets the user identifier.
	 *
	 * @param string $identifier User identifier
	 */
	public function setIdentifier(string $identifier)
	{
		if(!in_array($identifier, ['email', 'username', 'id']))
		{
			throw new InvalidArgumentException(vsprintf("%s(): Invalid identifier [ %s ].", [__METHOD__, $identifier]));
		}

		$this->identifier = $identifier;
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

	/**
	 * Fetches a user by its action token.
	 *
	 * @param  string                                   $token Action token
	 * @return \mako\gatekeeper\entities\user\User|bool
	 */
	public function getByActionToken(string $token)
	{
		return $this->getModel()->where('action_token', '=', $token)->first();
	}

	/**
	 * Fetches a user by its access token.
	 *
	 * @param  string                                   $token Access token
	 * @return \mako\gatekeeper\entities\user\User|bool
	 */
	public function getByAccessToken(string $token)
	{
		return $this->getModel()->where('access_token', '=', $token)->first();
	}

	/**
	 * Fetches a user by its email address.
	 *
	 * @param  string                                   $email Email address
	 * @return \mako\gatekeeper\entities\user\User|bool
	 */
	public function getByEmail(string $email)
	{
		return $this->getModel()->where('email', '=', $email)->first();
	}

	/**
	 * Fetches a user by its username.
	 *
	 * @param  string                                   $username Username
	 * @return \mako\gatekeeper\entities\user\User|bool
	 */
	public function getByUsername(string $username)
	{
		return $this->getModel()->where('username', '=', $username)->first();
	}

	/**
	 * Fetches a user by its id.
	 *
	 * @param  int                                      $id User id
	 * @return \mako\gatekeeper\entities\user\User|bool
	 */
	public function getById(int $id)
	{
		return $this->getModel()->where('id', '=', $id)->first();
	}
}
