<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth;

use \LogicException;

use \mako\http\Request;
use \mako\http\Response;
use \mako\session\Session;
use \mako\utility\DateTime;

/**
 * Gatekeeper authentication.
 *
 * @author  Frederic G. Østby
 */

class Gatekeeper
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Have we checked for a valid login?
	 * 
	 * @var boolean
	 */

	protected $isChecked = false;

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
	 * Auth key.
	 * 
	 * @var string
	 */

	protected $authKey = 'gatekeeper_auth_key';

	/**
	 * User model class.
	 * 
	 * @var string
	 */

	protected $userModel = '\mako\auth\models\User';

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
		'httponly' => false,
	];

	/**
	 * User instance.
	 * 
	 * @var \mako\auth\models\User
	 */

	protected $user;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\http\Request     $request   Request instance
	 * @param   \mako\http\Response    $response  Response instance
	 * @param   \mako\session\Session  $session   Session instance
	 */

	public function __construct(Request $request, Response $response, Session $session)
	{
		$this->request  = $request;
		$this->response = $response;
		$this->session  = $session;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sets the auth key.
	 * 
	 * @access  public
	 * @param   string  $authKey  Auth key
	 */

	public function setAuthKey($authKey)
	{
		if($this->isChecked)
		{
			throw new LogicException(vsprintf("%s(): Unable to alter auth key after login check.", [__METHOD__]));
		}

		$this->authKey = $authKey;
	}

	/**
	 * Sets the user model.
	 * 
	 * @access  public
	 * @param   string  $userModel  User model
	 */

	public function setUserModel($userModel)
	{
		if($this->isChecked)
		{
			throw new LogicException(vsprintf("%s(): Unable to alter user model after login check.", [__METHOD__]));
		}

		$this->userModel = $userModel;
	}

	/**
	 * Sets cookie options.
	 * 
	 * @access  public
	 * @param   array   $cookieOptions  Cookie options
	 */

	public function setCookieOptions(array $cookieOptions)
	{
		if($this->isChecked)
		{
			throw new LogicException(vsprintf("%s(): Unable to alter cookie options after login check.", [__METHOD__]));
		}

		$this->cookieOptions = $cookieOptions;
	}

	/**
	 * Creates a new user and returns the user object.
	 * 
	 * @access  public
	 * @param   string                  $email     Email address
	 * @param   string                  $username  Username
	 * @param   string                  $password  Password
	 * @param   boolean                 $activate  (optional) Will activate the user if set to true
	 * @return  \mako\auth\models\User
	 */

	public function createUser($email, $username, $password, $activate = false)
	{
		$user = new $this->userModel;

		$user->email      = $email;
		$user->username   = $username;
		$user->password   = $password;
		$user->created_at = new DateTime();
		$user->ip         = $this->request->ip();

		// Activate the user if nessesary

		if($activate === true)
		{
			$user->activate();
		}

		// Save the user, generate an auth token and save again

		$user->save();

		$user->generateToken();

		$user->save();

		// Return the newly created user

		return $user;
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
		$model = $this->userModel;

		$user = $model::where('token', '=', $token)->where('activated', '=', 0)->first();

		if(!$user)
		{
			// No such token exists. Return FALSE

			return false;
		}
		else
		{
			// Activate the user and generate a new auth token. Return TRUE

			$user->activate();

			$user->generateToken();

			$user->save();

			return true;
		}
	}

	/**
	 * Checks if a user is logged in.
	 * 
	 * @access  protected
	 * @return  \gatekeeper\models\User|null
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
				$model = $this->userModel;

				$this->user = $model::where('token', '=', $token)->first();

				if($this->user === false || $this->user->isBanned() || !$this->user->isActivated())
				{
					$this->logout();
				}
			}

			// Set checked status to TRUE

			$this->isChecked = true;
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
	 * @return  null|\mako\auth\models\User
	 */

	public function user()
	{
		return $this->check();
	}

	/**
	 * Returns TRUE if the email + password combination matches and the user is activated and not banned.
	 * A status code (LOGIN_ACTIVATING, LOGIN_BANNED or LOGIN_INCORRECT) will be retured in all other situations.
	 * 
	 * @access  protected
	 * @param   string       $email     User email
	 * @param   string       $password  User password
	 * @param   boolean      $force     (optional) Skip the password check?
	 * @return  boolean|int
	 */

	protected function authenticate($email, $password, $force = false)
	{
		$model = $this->userModel;

		$user = $model::where('email', '=', $email)->first();

		if($user !== false && ($user->validatePassword($password) || $force))
		{
			if(!$user->isActivated())
			{
				return static::LOGIN_ACTIVATING;
			}

			if($user->isBanned())
			{
				return static::LOGIN_BANNED;
			}

			$this->user = $user;

			return true;
		}

		return static::LOGIN_INCORRECT;
	}

	/**
	 * Logs in a user with a valid email/password combination.
	 * Returns TRUE if the email + password combination matches and the user is activated and not banned.
	 * A status code (LOGIN_ACTIVATING, LOGIN_BANNED or LOGIN_INCORRECT) will be retured in all other situations.
	 * 
	 * @access  public
	 * @param   string       $email     User email
	 * @param   string       $password  User password
	 * @param   boolean      $remember  (optional) Set a remember me cookie?
	 * @param   boolean      $force     (optional) Login the user without checking the password?
	 * @return  boolean|int
	 */

	public function login($email, $password, $remember = false, $force = false)
	{
		$authenticated = $this->authenticate($email, $password, $force);

		if($authenticated === true)
		{
			$this->session->regenerateId();

			$this->session->put($this->authKey, $this->user->token);

			if($remember === true)
			{
				$this->response->signedCookie($this->authKey, $this->user->token, (3600 * 24 * 365), $this->cookieOptions);
			}

			return true;
		}

		return $authenticated;
	}

	/**
	 * Login a user without checking the password.
	 * 
	 * @access  public
	 * @param   mixed    $identifier  User email or id
	 * @param   boolean  $remember    (optional) Set a remember me cookie?
	 * @return  boolean
	 */

	public function forceLogin($email, $remember = false)
	{
		return ($this->login($email, null, $remember, true) === true);
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

		$this->session->remove($this->authKey);

		$this->response->deleteCookie($this->authKey, $this->cookieOptions);

		$this->user = null;
	}
}
