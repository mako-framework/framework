<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\providers;

use \mako\auth\providers\UserProviderInterface;
use \mako\auth\user\UserInterface;
use \mako\security\Password;

/**
 * User provider.
 *
 * @author  Frederic G. Østby
 */

class UserProvider implements UserProviderInterface
{
	/**
	 * Model.
	 * 
	 * @var string
	 */

	protected $model;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $model  Model class
	 */

	public function __construct($model)
	{
		$this->model = $model;
	}

	/**
	 * {@inheritdoc}
	 */

	public function createUser($email, $username, $password, $ip = null)
	{
		$model = $this->model;

		$user = new $model;

		$user->setEmail($email);

		$user->setUsername($username);

		$user->setPassword($password);

		$user->setIp($ip);

		$user->save();

		return $user;
	}

	/**
	 * {@inheritdoc}
	 */

	public function getByActionToken($token)
	{
		$model = $this->model;

		return $model::where('action_token', '=', $token)->first();
	}

	/**
	 * {@inheritdoc}
	 */

	public function getByAccessToken($token)
	{
		$model = $this->model;

		return $model::where('access_token', '=', $token)->first();
	}

	/**
	 * {@inheritdoc}
	 */

	public function getByEmail($email)
	{
		$model = $this->model;

		return $model::where('email', '=', $email)->first();
	}

	/**
	 * {@inheritdoc}
	 */

	public function getByUsername($username)
	{
		$model = $this->model;

		return $model::where('username', '=', $username)->first();
	}

	/**
	 * {@inheritdoc}
	 */

	public function getById($id)
	{
		$model = $this->model;

		return $model::where('id', '=', $id)->first();
	}

	/**
	 * {@inheritdoc}
	 */

	public function validatePassword(UserInterface $user, $password)
	{
		return Password::validate($password, $user->getPassword());
	}
}