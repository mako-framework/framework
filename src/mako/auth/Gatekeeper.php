<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth;

use RuntimeException;

use mako\auth\providers\GroupProviderInterface;
use mako\auth\providers\UserProviderInterface;
use mako\http\Request;
use mako\http\Response;
use mako\session\Session;

/**
 * Gatekeeper authentication.
 *
 * @author  Frederic G. Østby
 */

class Gatekeeper
{
	/**
	 * Status code for banned users.
	 *
	 * @var int
	 */

	const LOGIN_BANNED = 100;

	/**
	 * Status code for users who need to activate their account.
	 *
	 * @var int
	 */

	const LOGIN_ACTIVATING = 101;

	/**
	 * Status code for users who fail to provide the correct credentials.
	 *
	 * @var int
	 */

	const LOGIN_INCORRECT = 102;

	/**
	 * Status code for users that are temporarily locked.
	 *
	 * @var int
	 */

	const LOGIN_LOCKED = 103;

	/**
	 * Request instance.
	 *
	 * @var \mako\http\Request
	 */

	protected $request;

	/**
	 * Response instance.
	 *
	 * @var \mako\http\Response
	 */

	protected $response;

	/**
	 * Session instance.
	 *
	 * @var \mako\session\Session
	 */

	protected $session;

	/**
	 * User provider.
	 *
	 * @var \mako\auth\UserProviderInterface
	 */

	protected $userProvider;

	/**
	 * Group provider.
	 *
	 * @var \mako\auth\GroupProviderInterface
	 */

	protected $groupProvider;

	/**
	 * Identifier.
	 *
	 * @var string
	 */

	protected $identifier = 'email';

	/**
	 * Auth key.
	 *
	 * @var string
	 */

	protected $authKey = 'gatekeeper_auth_key';

	/**
	 * Is brute force throttling enabled?
	 *
	 * @var boolean
	 */

	protected $throttle = false;

	/**
	 * Maximum number of login attempts before the account gets locked.
	 *
	 * @var int
	 */

	protected $maxLoginAttempts = 5;

	/**
	 * Number of seconds for which the account gets locked after
	 * reaching the maximum number of login attempts.
	 *
	 * @var int
	 */

	protected $lockTime = 300;

	/**
	 * Cookie options.
	 *
	 * @var array
	 */

	protected $cookieOptions =
	[
		'path'     => '/',
		'domain'   => '',
		'secure'   => false,
		'httponly' => true,
	];

	/**
	 * User instance.
	 *
	 * @var \mako\auth\user\User
	 */

	protected $user;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\http\Request                           $request        Request instance
	 * @param   \mako\http\Response                          $response       Response instance
	 * @param   \mako\session\Session                        $session        Session instance
	 * @param   \mako\auth\providers\UserProviderInterface   $userProvider   User provider
	 * @param   \mako\auth\providers\GroupProviderInterface  $groupProvider  Group provider
	 * @param   array                                        $options        Options
	 */

	public function __construct(Request $request, Response $response, Session $session, UserProviderInterface $userProvider, GroupProviderInterface $groupProvider, array $options = [])
	{
		$this->request       = $request;
		$this->response      = $response;
		$this->session       = $session;
		$this->userProvider  = $userProvider;
		$this->groupProvider = $groupProvider;

		$this->configure($options);
	}

	/**
	 * Configures the gatekeeper.
	 *
	 * @access  protected
	 * @param   array      $options  Options
	 */

	protected function configure(array $options)
	{
		// Configure throttling

		if(isset($options['throttling']))
		{
			$this->throttle = isset($options['throttling']['enabled']) && $options['throttling']['enabled'] === true;

			isset($options['throttling']['max_attemps']) && $this->maxLoginAttempts = $options['throttling']['max_attemps'];

			isset($options['throttling']['lock_time']) && $this->lockTime = $options['throttling']['lock_time'];
		}

		// Configure the identifier

		isset($options['identifier']) && $this->identifier = $options['identifier'];

		// Configure the authentication key

		isset($options['auth_key']) && $this->authKey = $options['auth_key'];

		// Configure the cookie options

		isset($options['cookie']) && $this->cookieOptions = $options['cookie'];
	}

	/**
	 * Returns the user provider instance.
	 *
	 * @access  public
	 * @return  \mako\auth\providers\UserProviderInterface
	 */

	public function getUserProvider()
	{
		return $this->userProvider;
	}

	/**
	 * Returns the group provider instance.
	 *
	 * @access  public
	 * @return  \mako\auth\providers\GroupProviderInterface
	 */

	public function getGroupProvider()
	{
		return $this->groupProvider;
	}

	/**
	 * Creates a new user and returns the user object.
	 *
	 * @access  public
	 * @param   string                         $email     Email address
	 * @param   string                         $username  Username
	 * @param   string                         $password  Password
	 * @param   boolean                        $activate  Will activate the user if set to true
	 * @return  \mako\auth\user\UserInterface
	 */

	public function createUser($email, $username, $password, $activate = false)
	{
		$user = $this->userProvider->createUser($email, $username, $password, $this->request->ip());

		$user->generateActionToken();

		$user->generateAccessToken();

		if($activate)
		{
			$user->activate();
		}

		$user->save();

		return $user;
	}

	/**
	 * Creates a new group and returns the group object.
	 *
	 * @access  public
	 * @param   string                           $name  Group name
	 * @return  \mako\auth\group\GroupInterface
	 */

	public function createGroup($name)
	{
		$group = $this->groupProvider->createGroup($name);

		return $group;
	}

	/**
	 * Activates a user based on the provided auth token.
	 *
	 * @access  public
	 * @param   string   $token  Auth token
	 * @return  boolean
	 */

	public function activateUser($token)
	{
		$user = $this->userProvider->getByActionToken($token);

		if($user === false)
		{
			return false;
		}
		else
		{
			$user->activate();

			$user->generateActionToken();

			$user->save();

			return $user;
		}
	}

	/**
	 * Checks if a user is logged in.
	 *
	 * @access  protected
	 * @return  mako\auth\user\UserInterface|null
	 */

	protected function check()
	{
		if(empty($this->user))
		{
			// Check if there'a user that can be logged in

			$token = $this->session->get($this->authKey, false);

			if($token === false)
			{
				$token = $this->request->signedCookie($this->authKey, false);

				if($token !== false)
				{
					$this->session->put($this->authKey, $token);
				}
			}

			if($token !== false)
			{
				$user = $this->userProvider->getByAccessToken($token);

				if($user === false || $user->isBanned() || !$user->isActivated())
				{
					$this->logout();
				}
				else
				{
					$this->user = $user;
				}
			}
		}

		return $this->user;
	}

	/**
	 * Returns FALSE if the user is logged in and TRUE if not.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isGuest()
	{
		return $this->check() === null;
	}

	/**
	 * Returns FALSE if the user isn't logged in and TRUE if it is.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isLoggedIn()
	{
		return $this->check() !== null;
	}

	/**
	 * Returns the authenticated user or NULL if no user is logged in.
	 *
	 * @access  public
	 * @return  null|\mako\auth\user\UserInterface
	 */

	public function getUser()
	{
		return $this->check();
	}

	/**
	 * Gets a user by its unique identifier.
	 *
	 * @access  protected
	 * @param   string                                 $identifier  User identifier
	 * @return  \mako\auth\user\UserInterface|boolean
	 */

	protected function getByIdentifier($identifier)
	{
		switch($this->identifier)
		{
			case 'email':
				return $this->userProvider->getByEmail($identifier);
			case 'username':
				return $this->userProvider->getByUsername($identifier);
			default:
				throw new RuntimeException(vsprintf("%s(): Unsupported identifier type [ %s ].", [__METHOD__, $identifier]));
		}
	}

	/**
	 * Returns TRUE if the email + password combination matches and the user is activated and not banned.
	 * A status code (LOGIN_ACTIVATING, LOGIN_BANNED or LOGIN_INCORRECT) will be retured in all other situations.
	 *
	 * @access  protected
	 * @param   string       $identifier  User email or username
	 * @param   string       $password    User password
	 * @param   boolean      $force       Skip the password check?
	 * @return  boolean|int
	 */

	protected function authenticate($identifier, $password, $force = false)
	{
		$user = $this->getByIdentifier($identifier);

		if($user !== false)
		{
			if($this->throttle && $user->isLocked())
			{
				return static::LOGIN_LOCKED;
			}

			if($this->userProvider->validatePassword($user, $password) || $force)
			{
				if(!$user->isActivated())
				{
					return static::LOGIN_ACTIVATING;
				}

				if($user->isBanned())
				{
					return static::LOGIN_BANNED;
				}

				if($this->throttle)
				{
					$this->userProvider->resetThrottle($user);
				}

				$this->user = $user;

				return true;
			}
			else
			{
				if($this->throttle)
				{
					$this->userProvider->throttle($user, $this->maxLoginAttempts, $this->lockTime);
				}
			}
		}

		return static::LOGIN_INCORRECT;
	}

	/**
	 * Logs in a user with a valid email/password combination.
	 * Returns TRUE if the email + password combination matches and the user is activated and not banned.
	 * A status code (LOGIN_ACTIVATING, LOGIN_BANNED or LOGIN_INCORRECT, LOGIN_LOCKED) will be retured in all other situations.
	 *
	 * @access  public
	 * @param   string       $identifier  User email
	 * @param   string       $password    User password
	 * @param   boolean      $remember    Set a remember me cookie?
	 * @param   boolean      $force       Login the user without checking the password?
	 * @return  boolean|int
	 */

	public function login($identifier, $password, $remember = false, $force = false)
	{
		if(empty($identifier))
		{
			return static::LOGIN_INCORRECT;
		}

		$authenticated = $this->authenticate($identifier, $password, $force);

		if($authenticated === true)
		{
			$this->session->regenerateId();

			$this->session->regenerateToken();

			$this->session->put($this->authKey, $this->user->getAccessToken());

			if($remember === true)
			{
				$this->response->signedCookie($this->authKey, $this->user->getAccessToken(), (3600 * 24 * 365), $this->cookieOptions);
			}

			return true;
		}

		return $authenticated;
	}

	/**
	 * Login a user without checking the password.
	 *
	 * @access  public
	 * @param   mixed    $identifier  User email or username
	 * @param   boolean  $remember    Set a remember me cookie?
	 * @return  boolean
	 */

	public function forceLogin($identifier, $remember = false)
	{
		return ($this->login($identifier, null, $remember, true) === true);
	}

	/**
	 * Builds and returns a basic HTTP authentication response.
	 *
	 * @access  protected
	 * @return  \mako\http\Response
	 */

	protected function basicHTTPAuthenticationResponse()
	{
		$response = new Response($this->request);

		$response->body('Authentication required.');

		$response->header('www-authenticate', 'basic');

		$response->status(401);

		return $response;
	}

	/**
	 * Returns a basic authentication response if login is required and NULL if not.
	 *
	 * @access  public
	 * @return  \mako\http\Response|null
	 */

	public function basicAuth()
	{
		if($this->isLoggedIn() || $this->login($this->request->username(), $this->request->password()) === true)
		{
			return;
		}

		return $this->basicHTTPAuthenticationResponse();
	}

	/**
	 * Logs the user out.
	 *
	 * @access  public
	 */

	public function logout()
	{
		$this->session->regenerateId();

		$this->session->regenerateToken();

		$this->session->remove($this->authKey);

		$this->response->deleteCookie($this->authKey, $this->cookieOptions);

		$this->user = null;
	}
}