<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\providers;

use \mako\auth\user\UserInterface;

/**
 * User provider interface.
 *
 * @author  Frederic G. Østby
 */

interface UserProviderInterface
{
	public function createUser($email, $username, $password, $ip);
	public function getByActionToken($token);
	public function getByAccessToken($token);
	public function getByEmail($email);
	public function validatePassword(UserInterface $user, $password);
}