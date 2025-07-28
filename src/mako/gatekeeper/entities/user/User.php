<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\entities\user;

use DateTimeInterface;
use mako\chrono\Time;
use mako\database\midgard\ORM;
use mako\database\midgard\relations\ManyToMany;
use mako\database\midgard\traits\SensitiveStringTrait;
use mako\database\midgard\traits\TimestampedTrait;
use mako\gatekeeper\authorization\AuthorizableInterface;
use mako\gatekeeper\authorization\traits\AuthorizableTrait;
use mako\gatekeeper\entities\group\Group;
use mako\gatekeeper\exceptions\GatekeeperException;
use mako\security\password\Bcrypt;
use mako\security\password\HasherInterface;
use SensitiveParameter;

use function hash;
use function is_int;
use function random_bytes;
use function time;

/**
 * User.
 *
 * @method   int                              getId()
 * @property int                              $id
 * @property Time                             $created_at
 * @property Time                             $updated_at
 * @property string                           $email
 * @property string                           $username
 * @property string                           $password
 * @property string                           $ip
 * @property string                           $action_token
 * @property string                           $access_token
 * @property int                              $activated
 * @property int                              $banned
 * @property int                              $failed_attempts
 * @property Time|null                        $last_fail_at
 * @property Time|null                        $locked_until
 * @property \mako\database\midgard\ResultSet $groups
 */
class User extends ORM implements AuthorizableInterface, MemberInterface, UserEntityInterface
{
	use AuthorizableTrait;
	use SensitiveStringTrait;
	use TimestampedTrait;

	/**
	 * Table name.
	 */
	protected string $tableName = 'users';

	/**
	 * Type casting.
	 */
	protected array $cast = ['last_fail_at' => 'date', 'locked_until' => 'date'];

	/**
	 * Sensitive strings.
	 */
	protected array $sensitiveStrings = ['password', 'action_token', 'access_token'];

	/**
	 * User groups.
	 */
	public function groups(): ManyToMany
	{
		return $this->manyToMany(Group::class);
	}

	/**
	 * Returns a hasher instance.
	 */
	protected function getHasher(): HasherInterface
	{
		return new Bcrypt;
	}

	/**
	 * Password mutator.
	 */
	protected function passwordMutator(#[SensitiveParameter] string $password): string
	{
		return $this->getHasher()->create($password);
	}

	/**
	 * Generates a new token.
	 */
	protected function generateToken(): string
	{
		return hash('sha256', random_bytes(32));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getId(): mixed
	{
		return $this->id;
	}

	/**
	 * Sets the user email address.
	 */
	public function setEmail(string $email): void
	{
		$this->email = $email;
	}

	/**
	 * Returns the user email address.
	 */
	public function getEmail(): string
	{
		return $this->email;
	}

	/**
	 * Sets the username.
	 */
	public function setUsername(string $username): void
	{
		$this->username = $username;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUsername(): string
	{
		return $this->username;
	}

	/**
	 * Sets the user password.
	 */
	public function setPassword(#[SensitiveParameter] string $password): void
	{
		$this->password = $password;
	}

	/**
	 * Returns the user password hash.
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * Sets the user IP address.
	 */
	public function setIp(string $ip): void
	{
		$this->ip = $ip;
	}

	/**
	 * Returns the user IP address.
	 */
	public function getIp(): string
	{
		return $this->ip;
	}

	/**
	 * Generates a new action token.
	 */
	public function generateActionToken(): string
	{
		return $this->action_token = $this->generateToken();
	}

	/**
	 * Returns the user action token.
	 */
	public function getActionToken(): string
	{
		return $this->action_token;
	}

	/**
	 * Generates a new access token.
	 */
	public function generateAccessToken(): string
	{
		return $this->access_token = $this->generateToken();
	}

	/**
	 * Returns the user access token.
	 */
	public function getAccessToken(): string
	{
		return $this->access_token;
	}

	/**
	 * Activates the user.
	 */
	public function activate(): void
	{
		$this->activated = 1;
	}

	/**
	 * Deactivates the user.
	 */
	public function deactivate(): void
	{
		$this->activated = 0;
	}

	/**
	 * Returns TRUE of the user is activated and FALSE if not.
	 */
	public function isActivated(): bool
	{
		return $this->activated == 1;
	}

	/**
	 * Bans the user.
	 */
	public function ban(): void
	{
		$this->banned = 1;
	}

	/**
	 * Unbans the user.
	 */
	public function unban(): void
	{
		$this->banned = 0;
	}

	/**
	 * Returns TRUE if the user is banned and FALSE if not.
	 */
	public function isBanned(): bool
	{
		return $this->banned == 1;
	}

	/**
	 * Returns TRUE if the provided password is correct and FALSE if not.
	 */
	public function validatePassword(#[SensitiveParameter] string $password, bool $autoSave = true): bool
	{
		$hasher = $this->getHasher();

		$isValid = $hasher->verify($password, $this->password);

		// Check if the password needs to be rehashed if the provided password is valid

		if ($isValid && $hasher->needsRehash($this->password)) {
			$this->password = $password;

			if ($autoSave) {
				$this->save();
			}
		}

		// Return validation result

		return $isValid;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isMemberOf($group): bool
	{
		if (!$this->isPersisted) {
			throw new GatekeeperException('You can only check memberships for users that exist in the database.');
		}

		foreach ((array) $group as $check) {
			/** @var \mako\gatekeeper\entities\group\GroupEntityInterface&ORM $userGroup */
			foreach ($this->groups as $userGroup) {
				if ((is_int($check) && (int) $userGroup->getId() === $check) || $userGroup->getName() === $check) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Locks the account until the given date.
	 */
	public function lockUntil(DateTimeInterface $time): void
	{
		$this->locked_until = $time;
	}

	/**
	 * Returns null if the account isn't locked and a date time instance if it's locked.
	 */
	public function lockedUntil(): null|DateTimeInterface|Time
	{
		return $this->locked_until;
	}

	/**
	 * Unlocks the account.
	 */
	public function unlock(): void
	{
		$this->locked_until = null;
	}

	/**
	 * Returns TRUE if the account is locked and FALSE if not.
	 */
	public function isLocked(): bool
	{
		return $this->locked_until !== null && $this->locked_until->getTimestamp() >= time();
	}

	/**
	 * Returns the number of failed login attempts.
	 */
	public function getFailedAttempts(): int
	{
		return $this->failed_attempts;
	}

	/**
	 * Gets the time of the last failed attempt.
	 */
	public function getLastFailAt(): null|DateTimeInterface|Time
	{
		return $this->last_fail_at;
	}

	/**
	 * Throttles login attempts.
	 */
	public function throttle(int $maxLoginAttempts, int $lockTime, bool $autoSave = true): bool
	{
		$now = Time::now();

		// Reset the failed attempt count if the last failed attempt was more than $lockTime seconds ago

		if ($this->last_fail_at !== null) {
			if (($now->getTimestamp() - $this->last_fail_at->getTimestamp()) > $lockTime) {
				$this->failed_attempts = 0;
			}
		}

		// Increment the failed attempt count and update the last fail time

		$this->failed_attempts++;

		$this->last_fail_at = $now;

		// Lock the account for $lockTime seconds if we have exeeded the maximum number of login attempts

		if ($this->failed_attempts >= $maxLoginAttempts) {
			$this->locked_until = (clone $now)->forward($lockTime);
		}

		// Save the changes to the user if autosave is enabled

		return $autoSave ? $this->save() : true;
	}

	/**
	 * Resets the login throttling.
	 */
	public function resetThrottle(bool $autoSave = true): bool
	{
		if ($this->failed_attempts > 0) {
			$this->failed_attempts = 0;

			$this->last_fail_at = null;

			$this->locked_until = null;

			return $autoSave ? $this->save() : true;
		}

		return true;
	}
}
