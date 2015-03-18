<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\providers;

use mako\auth\user\UserInterface;

/**
 * User provider interface.
 *
 * @author  Frederic G. Østby
 */

interface UserProviderInterface
{
	/**
	 * Creates and returns a user.
	 *
	 * @access  public
	 * @param   string                         $email     Email address
	 * @param   string                         $username  Username
	 * @param   string                         $password  Password
	 * @param   string                         $ip        IP address
	 * @return  \mako\auth\user\UserInterface
	 */

	public function createUser($email, $username, $password, $ip = null);

	/**
	 * Fetches a user by its action token.
	 *
	 * @access  public
	 * @param   string                                 $token  Action token
	 * @return  \mako\auth\user\UserInterface|boolean
	 */

	public function getByActionToken($token);

	/**
	 * Fetches a user by its access token.
	 *
	 * @access  public
	 * @param   string                                 $token  Access token
	 * @return  \mako\auth\user\UserInterface|boolean
	 */

	public function getByAccessToken($token);

	/**
	 * Fetches a user by its email address.
	 *
	 * @access  public
	 * @param   string                                 $email  Email address
	 * @return  \mako\auth\user\UserInterface|boolean
	 */

	public function getByEmail($email);

	/**
	 * Fetches a user by its username.
	 *
	 * @access  public
	 * @param   string                                 $username  Username
	 * @return  \mako\auth\user\UserInterface|boolean
	 */

	public function getByUsername($username);

	/**
	 * Fetches a user by its id.
	 *
	 * @access  public
	 * @param   string                                 $id  User id
	 * @return  \mako\auth\user\UserInterface|boolean
	 */

	public function getById($id);

	/**
	 * Throttles login attempts.
	 *
	 * @access  public
	 * @param   \mako\auth\user\UserInterface  $user              User object
	 * @param   int                            $maxLoginAttempts  Maximum number of failed login attempts
	 * @param   int                            $lockTime          Number of seconds for which the account gets locked after reaching the maximum number of login attempts
	 * @return  boolean
	 */

	public function throttle(UserInterface $user, $maxLoginAttempts, $lockTime);

	/**
	 * Resets the login throttling.
	 *
	 * @access  public
	 * @param   \mako\auth\user\UserInterface  $user  User object
	 * @return  boolean
	 */

	public function resetThrottle(UserInterface $user);

	/**
	 * Validates a user password.
	 *
	 * @access  public
	 * @param   \mako\auth\user\UserInterface  $user      User object
	 * @param   string                         $password  Password
	 * @return  boolean
	 */

	public function validatePassword(UserInterface $user, $password);
}