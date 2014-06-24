<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\user;

use \LogicException;

use \mako\security\Password;
use \mako\utility\UUID;

/**
 * User.
 *
 * @author  Frederic G. Østby
 */

class User extends \mako\database\midgard\ORM implements \mako\auth\user\UserInterface
{
	use \mako\database\midgard\traits\TimestampedTrait;

	/**
	 * Table name.
	 * 
	 * @var string
	 */

	protected $tableName = 'users';

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
	 * Returns the user id.
	 * 
	 * @access  public
	 * @return  int|string
	 */

	public function getId()
	{
		$this->getPrimaryKeyValue();
	}

	/**
	 * Sets the user email address.
	 * 
	 * @access  public
	 * @param   string  $email  Email address
	 */

	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * Returns the user email address.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Sets the user username.
	 * 
	 * @access  public
	 * @param   string  $username  Username
	 */

	public function setUsername($username)
	{
		$this->username = $username;
	}

	/**
	 * Returns the user username.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Sets the user password.
	 * 
	 * @access  public
	 * @param   string  $password  Password
	 */

	public function setPassword($password)
	{
		$this->password = $password;
	}

	/**
	 * Returns the user password.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Sets the user IP address.
	 * 
	 * @access  public
	 * @param   string  $ip  IP address
	 */

	public function setIp($ip)
	{
		$this->ip = $ip;
	}

	/**
	 * Returns the user IP address.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getIp()
	{
		return $this->ip;
	}

	/**
	 * Generates a new action token.
	 * 
	 * @access  public
	 */

	public function generateActionToken()
	{
		$this->action_token = $this->generateToken();
	}

	/**
	 * Returns the user action token.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getActionToken()
	{
		return $this->action_token;
	}

	/**
	 * Generates a new access token.
	 * 
	 * @access  public
	 */

	public function generateAccessToken()
	{
		$this->access_token = $this->generateToken();
	}

	/**
	 * Returns the user access token.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getAccessToken()
	{
		return $this->access_token;
	}

	/**
	 * Activates the user.
	 * 
	 * @access  public
	 */

	public function activate()
	{
		$this->activated = 1;
	}

	/**
	 * Deactivates the user.
	 * 
	 * @access  public
	 */

	public function deactivate()
	{
		$this->activated = 0;
	}

	/**
	 * Returns TRUE of the user is activated and FALSE if not.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function isActivated()
	{
		return $this->activated == 1;
	}

	/**
	 * Bans the user.
	 * 
	 * @access  public
	 */

	public function ban()
	{
		$this->banned = 1;
	}

	/**
	 * Unbans the user.
	 * 
	 * @access  public
	 */

	public function unban()
	{
		$this->banned = 0;
	}

	/**
	 * Returns TRUE if the user is banned and FALSE if not.
	 * 
	 * @return  boolean
	 */

	public function isBanned()
	{
		$this->banned == 1;
	}
}