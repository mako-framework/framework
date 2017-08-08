<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\entities\user;

use DateTimeInterface;
use LogicException;

use mako\chrono\Time;
use mako\database\midgard\ORM;
use mako\database\midgard\relations\ManyToMany;
use mako\database\midgard\traits\TimestampedTrait;
use mako\gatekeeper\entities\group\Group;
use mako\gatekeeper\entities\user\MemberInterface;
use mako\gatekeeper\entities\user\UserEntityInterface;
use mako\security\Password;

/**
 * User.
 *
 * @author Frederic G. Østby
 *
 * @method int                                getId()
 * @property int                              $id
 * @property \mako\utility\Time               $created_at
 * @property \mako\utility\Time               $updated_at
 * @property string                           $email
 * @property string                           $password
 * @property string                           $ip
 * @property string                           $action_token
 * @property string                           $access_token
 * @property int                              $activated
 * @property int                              $banned
 * @property int                              $failed_attempts
 * @property \mako\utility\Time|null          $last_fail_at
 * @property \mako\utility\Time|null          $locked_until
 * @property \mako\database\midgard\ResultSet $groups
 */
class User extends ORM implements MemberInterface, UserEntityInterface
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
	 * User groups.
	 *
	 * @return \mako\database\midgard\relations\ManyToMany
	 */
	public function groups(): ManyToMany
	{
		return $this->manyToMany(Group::class);
	}

	/**
	 * Password mutator.
	 *
	 * @param  string $password Password
	 * @return string
	 */
	protected function passwordMutator(string $password): string
	{
		return Password::hash($password);
	}

	/**
	 * Generates a new token.
	 *
	 * @return string
	 */
	protected function generateToken(): string
	{
		return hash('sha256', random_bytes(32));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Sets the user email address.
	 *
	 * @param string $email Email address
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * Returns the user email address.
	 *
	 * @return string
	 */
	public function getEmail(): string
	{
		return $this->email;
	}

	/**
	 * Sets the username.
	 *
	 * @param string $username Username
	 */
	public function setUsername(string $username)
	{
		$this->username = $username;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUsername(): string
	{
		return $this->username;
	}

	/**
	 * Sets the user password.
	 *
	 * @param string $password Password
	 */
	public function setPassword(string $password)
	{
		$this->password = $password;
	}

	/**
	 * Returns the user password hash.
	 *
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * Sets the user IP address.
	 *
	 * @param string $ip IP address
	 */
	public function setIp(string $ip)
	{
		$this->ip = $ip;
	}

	/**
	 * Returns the user IP address.
	 *
	 * @return string
	 */
	public function getIp(): string
	{
		return $this->ip;
	}

	/**
	 * Generates a new action token.
	 *
	 * @return string
	 */
	public function generateActionToken(): string
	{
		return $this->action_token = $this->generateToken();
	}

	/**
	 * Returns the user action token.
	 *
	 * @return string
	 */
	public function getActionToken(): string
	{
		return $this->action_token;
	}

	/**
	 * Generates a new access token.
	 *
	 * @return string
	 */
	public function generateAccessToken(): string
	{
		return $this->access_token = $this->generateToken();
	}

	/**
	 * Returns the user access token.
	 *
	 * @return string
	 */
	public function getAccessToken(): string
	{
		return $this->access_token;
	}

	/**
	 * Activates the user.
	 */
	public function activate()
	{
		$this->activated = 1;
	}

	/**
	 * Deactivates the user.
	 */
	public function deactivate()
	{
		$this->activated = 0;
	}

	/**
	 * Returns TRUE of the user is activated and FALSE if not.
	 *
	 * @return bool
	 */
	public function isActivated(): bool
	{
		return $this->activated == 1;
	}

	/**
	 * Bans the user.
	 */
	public function ban()
	{
		$this->banned = 1;
	}

	/**
	 * Unbans the user.
	 */
	public function unban()
	{
		$this->banned = 0;
	}

	/**
	 * Returns TRUE if the user is banned and FALSE if not.
	 *
	 * @return bool
	 */
	public function isBanned(): bool
	{
		return $this->banned == 1;
	}

	/**
	 * Returns true if the provided password is correct and false if not.
	 *
	 * @param  string $password Privided password
	 * @param  bool   $autoSave Autosave rehashed password?
	 * @return bool
	 */
	public function validatePassword(string $password, $autoSave = true): bool
	{
		$isValid = Password::validate($password, $this->password);

		// Check if the password needs to be rehashed IF the provided password is valid

		if($isValid && Password::needsRehash($this->password))
		{
			$user->password = $password;

			if($autoSave)
			{
				$user->save();
			}
		}

		// Return validation result

		return $isValid;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isMemberOf($group): bool
	{
		if(!$this->exists)
		{
			throw new LogicException(vsprintf("%s(): You can only check memberships for users that exist in the database.", [__METHOD__]));
		}

		foreach((array) $group as $check)
		{
			foreach($this->groups as $userGroup)
			{
				if((is_int($check) && (int) $userGroup->getId() === $check) || $userGroup->getName() === $check)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Locks the account until the given date.
	 *
	 * @param \DateTimeInterface $time Date
	 */
	public function lockUntil(DateTimeInterface $time)
	{
		$this->locked_until = $time;
	}

	/**
	 * Returns null if the account isn't locked and a date time instance if its locked.
	 *
	 * @return null|\mako\chrono\Time|\DateTimeInterface
	 */
	public function lockedUntil()
	{
		return $this->locked_until;
	}

	/**
	 * Unlocks the account.
	 */
	public function unlock()
	{
		$this->locked_until = null;
	}

	/**
	 * Returns TRUE if the account is locked and FALSE if not.
	 *
	 * @return bool
	 */
	public function isLocked(): bool
	{
		return $this->locked_until !== null;
	}

	/**
	 * Returns the number of failed login attempts.
	 *
	 * @return int
	 */
	public function getFailedAttempts(): int
	{
		return $this->failed_attempts;
	}

	/**
	 * Gets the time of the last failed attempt.
	 *
	 * @return null|\mako\chrono\Time|\DateTimeInterface
	 */
	public function getLastFailAt()
	{
		return $this->last_fail_at;
	}

	/**
	 * Throttles login attempts.
	 *
	 * @param  int  $maxLoginAttempts Maximum number of failed login attempts
	 * @param  int  $lockTime         Number of seconds for which the account gets locked after reaching the maximum number of login attempts
	 * @param  bool $autoSave         Autosave changes?
	 * @return bool
	 */
	public function throttle(int $maxLoginAttempts, int $lockTime, bool $autoSave = true): bool
	{
		$now = Time::now();

		// Reset the failed attempt count if the last failed attempt was more than $lockTime seconds ago

		if($this->last_fail_at !== null)
		{
			if(($now->getTimestamp() - $this->last_fail_at->getTimestamp()) > $lockTime)
			{
				$this->failed_attempts = 0;
			}
		}

		// Increment the failed attempt count and update the last fail time

		$this->failed_attempts++;

		$this->last_fail_at = $now;

		// Lock the account for $lockTime seconds if we have exeeded the maximum number of login attempts

		if($this->failed_attempts >= $maxLoginAttempts)
		{
			$this->locked_until = (clone $now)->forward($lockTime);
		}

		// Save the changes to the user if autosave is enabled

		return $autoSave ? $this->save() : true;
	}

	/**
	 * Resets the login throttling.
	 *
	 * @param  bool $autoSave Autosave changes?
	 * @return bool
	 */
	public function resetThrottle(bool $autoSave = true): bool
	{
		if($this->failed_attempts > 0)
		{
			$this->failed_attempts = 0;

			$this->last_fail_at = null;

			$this->locked_until = null;

			return $autoSave ? $this->save() : true;
		}

		return true;
	}
}
