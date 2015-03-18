<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\providers;

use mako\auth\providers\UserProviderInterface;
use mako\auth\user\UserInterface;
use mako\chrono\Time;
use mako\security\Password;

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

	public function throttle(UserInterface $user, $maxLoginAttempts, $lockTime)
	{
		$now = Time::now();

		// Reset the failed attempt count if the last failed attempt was more than $lockTime seconds ago

		if(($lastFailAt = $user->getLastFailAt()) !== null)
		{
			if(($now->getTimestamp() - $lastFailAt->getTimestamp()) > $lockTime)
			{
				$user->resetFailedAttempts();
			}
		}

		// Increment the failed attempt count and update the last fail time

		$user->incrementFailedAttempts();

		$user->setLastFailAt($now);

		// Lock the account for $lockTime seconds if we have exeeded the maximum number of login attempts

		if($user->getFailedAttempts() >= $maxLoginAttempts)
		{
			$user->lockUntil(Time::now()->forward($lockTime));
		}

		// Save the changes to the user

		return $user->save();
	}

	/**
	 * {@inheritdoc}
	 */

	public function resetThrottle(UserInterface $user)
	{
		if($user->getFailedAttempts() > 0)
		{
			$user->resetFailedAttempts();

			$user->unlock();

			return $user->save();
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */

	public function validatePassword(UserInterface $user, $password)
	{
		$hash = $user->getPassword();

		// Check if the provided password is valid

		$isValid = Password::validate($password, $hash);

		// Check if the password needs to be rehashed IF the provided password is valid

		if($isValid && Password::needsRehash($hash))
		{
			$user->setPassword($password);

			$user->save();
		}

		// Return validation result

		return $isValid;
	}
}