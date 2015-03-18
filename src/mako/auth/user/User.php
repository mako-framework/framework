<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\user;

use DateTimeInterface;
use LogicException;

use mako\auth\group\MemberInterface;
use mako\auth\user\UserInterface;
use mako\chrono\Time;
use mako\database\midgard\ORM;
use mako\database\midgard\traits\TimestampedTrait;
use mako\security\Password;
use mako\utility\UUID;

/**
 * User.
 *
 * @author  Frederic G. Østby
 */

class User extends ORM implements UserInterface, MemberInterface
{
	use TimestampedTrait;

	/**
	 * Table name.
	 *
	 * @var string
	 */

	protected $tableName = 'users';

	/**
	 * Type casting.
	 *
	 * @var array
	 */

	protected $cast = ['last_fail_at' => 'date', 'locked_until' => 'date'];

	/**
	 * Password mutator.
	 *
	 * @access  protected
	 * @param   string     $password  Password
	 * @return  string
	 */

	protected function passwordMutator($password)
	{
		return Password::hash($password);
	}

	/**
	 * Generates a new token.
	 *
	 * @access  protected
	 * @return  string
	 */

	protected function generateToken()
	{
		if(!$this->exists)
		{
			throw new LogicException(vsprintf("%s(): You can only generate auth tokens for users that exist in the database.", [__METHOD__]));
		}

		return hash('sha256', UUID::v4() . $this->getId() . uniqid('token', true));
	}

	/**
	 * {@inheritdoc}
	 */

	public function getId()
	{
		return $this->getPrimaryKeyValue();
	}

	/**
	 * {@inheritdoc}
	 */

	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * {@inheritdoc}
	 */

	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * {@inheritdoc}
	 */

	public function setUsername($username)
	{
		$this->username = $username;
	}

	/**
	 * {@inheritdoc}
	 */

	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * {@inheritdoc}
	 */

	public function setPassword($password)
	{
		$this->password = $password;
	}

	/**
	 * {@inheritdoc}
	 */

	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * {@inheritdoc}
	 */

	public function setIp($ip)
	{
		$this->ip = $ip;
	}

	/**
	 * {@inheritdoc}
	 */

	public function getIp()
	{
		return $this->ip;
	}

	/**
	 * {@inheritdoc}
	 */

	public function generateActionToken()
	{
		$this->action_token = $this->generateToken();
	}

	/**
	 * {@inheritdoc}
	 */

	public function getActionToken()
	{
		return $this->action_token;
	}

	/**
	 * {@inheritdoc}
	 */

	public function generateAccessToken()
	{
		$this->access_token = $this->generateToken();
	}

	/**
	 * {@inheritdoc}
	 */

	public function getAccessToken()
	{
		return $this->access_token;
	}

	/**
	 * {@inheritdoc}
	 */

	public function activate()
	{
		$this->activated = 1;
	}

	/**
	 * {@inheritdoc}
	 */

	public function deactivate()
	{
		$this->activated = 0;
	}

	/**
	 * {@inheritdoc}
	 */

	public function isActivated()
	{
		return $this->activated == 1;
	}

	/**
	 * {@inheritdoc}
	 */

	public function ban()
	{
		$this->banned = 1;
	}

	/**
	 * {@inheritdoc}
	 */

	public function unban()
	{
		$this->banned = 0;
	}

	/**
	 * {@inheritdoc}
	 */

	public function isBanned()
	{
		return $this->banned == 1;
	}

	/**
	 * {@inheritdoc}
	 */

	public function incrementFailedAttempts()
	{
		$this->failed_attempts++;
	}

	/**
	 * {@inheritdoc}
	 */

	public function getFailedAttempts()
	{
		return $this->failed_attempts;
	}

	/**
	 * {@inheritdoc}
	 */

	public function resetFailedAttempts()
	{
		$this->failed_attempts = 0;
	}

	/**
	 * {@inheritdoc}
	 */

	public function setLastFailAt(DateTimeInterface $time)
	{
		$this->last_fail_at = $time;
	}

	/**
	 * {@inheritdoc}
	 */

	public function getLastFailAt()
	{
		return $this->last_fail_at;
	}

	/**
	 * {@inheritdoc}
	 */

	public function lockUntil(DateTimeInterface $time)
	{
		$this->locked_until = $time;
	}

	/**
	 * {@inheritdoc}
	 */

	public function unlock()
	{
		$this->locked_until = null;
	}

	/**
	 * {@inheritdoc}
	 */

	public function isLocked()
	{
		return $this->locked_until !== null && $this->locked_until->getTimestamp() >= Time::now()->getTimestamp();
	}

	public function isMemberOf($group)
	{
		if(!$this->exists)
		{
			throw new LogicException(vsprintf("%s(): You can only check memberships for users that exist in the database.", [__METHOD__]));
		}

		foreach((array) $group as $check)
		{
			foreach($this->groups as $userGroup)
			{
				if(is_int($check))
				{
					if((int) $userGroup->getId() === $check)
					{
						return true;
					}
				}
				else
				{
					if($userGroup->getName() === $check)
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * User groups.
	 *
	 * @access  public
	 * @return  \mako\database\midgard\relations\ManyToMany
	 */

	public function groups()
	{
		return $this->manyToMany('mako\auth\group\Group');
	}
}