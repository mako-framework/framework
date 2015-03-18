<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\user;

use DateTimeInterface;

/**
 * User interface.
 *
 * @author  Frederic G. Østby
 */

interface UserInterface
{
	/**
	 * Returns the user id.
	 *
	 * @access  public
	 * @return  int|string
	 */

	public function getId();

	/**
	 * Sets the user email address.
	 *
	 * @access  public
	 * @param   string  $email  Email address
	 */

	public function setEmail($email);

	/**
	 * Returns the user email address.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getEmail();

	/**
	 * Sets the user username.
	 *
	 * @access  public
	 * @param   string  $username  Username
	 */

	public function setUsername($username);

	/**
	 * Returns the user username.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getUsername();

	/**
	 * Sets the user password.
	 *
	 * @access  public
	 * @param   string  $password  Password
	 */

	public function setPassword($password);

	/**
	 * Returns the user password.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getPassword();

	/**
	 * Sets the user IP address.
	 *
	 * @access  public
	 * @param   string  $ip  IP address
	 */

	public function setIp($ip);

	/**
	 * Returns the user IP address.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getIp();

	/**
	 * Generates a new action token.
	 *
	 * @access  public
	 */

	public function generateActionToken();

	/**
	 * Returns the user action token.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getActionToken();

	/**
	 * Generates a new access token.
	 *
	 * @access  public
	 */

	public function generateAccessToken();

	/**
	 * Returns the user access token.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getAccessToken();

	/**
	 * Activates the user.
	 *
	 * @access  public
	 */

	public function activate();

	/**
	 * Deactivates the user.
	 *
	 * @access  public
	 */

	public function deactivate();

	/**
	 * Returns TRUE of the user is activated and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isActivated();

	/**
	 * Bans the user.
	 *
	 * @access  public
	 */

	public function ban();

	/**
	 * Unbans the user.
	 *
	 * @access  public
	 */

	public function unban();

	/**
	 * Returns TRUE if the user is banned and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isBanned();

	/**
	 * Increments the number of failed attempts.
	 *
	 * @access  public
	 */

	public function incrementFailedAttempts();

	/**
	 * Returns the number of failed login attempts.
	 *
	 * @access  public
	 * @return  int
	 */

	public function getFailedAttempts();

	/**
	 * Resets the number of failted attempts.
	 *
	 * @access  public
	 */

	public function resetFailedAttempts();

	/**
	 * Sets the time of the last failed attempt.
	 *
	 * @access  public
	 * @param   \DateTimeInterface  $time  Date
	 */

	public function setLastFailAt(DateTimeInterface $time);

	/**
	 * Gets the time of the last failed attempt.
	 *
	 * @access  public
	 * @return  null|\DateTimeInterface
	 */

	public function getLastFailAt();

	/**
	 * Locks the account until the given date.
	 *
	 * @access  public
	 * @param   \DateTimeInterface  $time  Date
	 */

	public function lockUntil(DateTimeInterface $time);

	/**
	 * Unlocks the account.
	 *
	 * @access  public
	 */

	public function unlock();

	/**
	 * Returns TRUE if the account is locked and FALSE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isLocked();

	/**
	 * Saves the member.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function save();

	/**
	 * Deletes the member.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function delete();
}