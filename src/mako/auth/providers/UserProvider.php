<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\providers;

use \mako\auth\user\UserInterface;
use \mako\security\Password;

/**
 * User provider.
 *
 * @author  Frederic G. Østby
 */

class UserProvider implements \mako\auth\providers\UserProviderInterface
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
	 * Creates and returns a user.
	 * 
	 * @access  public
	 * @param   string                         $email     Email address
	 * @param   string                         $username  Username
	 * @param   string                         $password  Password
	 * @param   string                         $ip        (optional) IP address
	 * @return  \mako\auth\user\UserInterface
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
	 * Fetches a user by its action token.
	 * 
	 * @access  public
	 * @param   string                                 $token  Action token
	 * @return  \mako\auth\user\UserInterface|boolean
	 */

	public function getByActionToken($token)
	{
		$model = $this->model;

		return $model::where('action_token', '=', $token)->first();
	}

	/**
	 * Fetches a user by its access token.
	 * 
	 * @access  public
	 * @param   string                                 $token  Access token
	 * @return  \mako\auth\user\UserInterface|boolean
	 */

	public function getByAccessToken($token)
	{
		$model = $this->model;

		return $model::where('access_token', '=', $token)->first();
	}

	/**
	 * Fetches a user by its email address.
	 * 
	 * @access  public
	 * @param   string                                 $email  Email address
	 * @return  \mako\auth\user\UserInterface|boolean
	 */

	public function getByEmail($email)
	{
		$model = $this->model;

		return $model::where('email', '=', $email)->first();
	}

	/**
	 * Fetches a user by its id.
	 * 
	 * @access  public
	 * @param   string                                 $id  User id
	 * @return  \mako\auth\user\UserInterface|boolean
	 */

	public function getById($id)
	{
		$model = $this->model;

		return $model::where('id', '=', $id)->first();
	}

	/**
	 * Validates a user password.
	 * 
	 * @access  public
	 * @param   \mako\auth\user\UserInterface  $user      User object
	 * @param   string                         $password  Password
	 * @return  boolean
	 */

	public function validatePassword(UserInterface $user, $password)
	{
		return Password::validate($password, $user->getPassword());
	}
}