<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\adapters;

use mako\gatekeeper\Authentication;
use mako\gatekeeper\adapters\Adapter;
use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\repositories\group\GroupRepository;
use mako\gatekeeper\repositories\user\UserRepository;
use mako\http\Request;
use mako\http\Response;
use mako\session\Session as HttpSession;

/**
 * Session adapter.
 *
 * @author Frederic G. Østby
 *
 * @method \mako\gatekeeper\entities\user\User|null getUser()
 */
class Session extends Adapter
{
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
	 * Adapter options.
	 *
	 * @var array
	 */
	protected $options =
	[
		'auth_key'       => 'gatekeeper_auth_key',
		'cookie_options' =>
		[
			'path'     => '/',
			'domain'   => '',
			'secure'   => false,
			'httponly' => true,
		],
		'throttling'     =>
		[
			'enabled'      => false,
			'max_attempts' => 5,
			'lock_time'    => 300,
		],
	];

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param \mako\gatekeeper\repositories\user\UserRepository   $userRepository  User repository
	 * @param \mako\gatekeeper\repositories\group\GroupRepository $groupRepository Group repository
	 * @param \mako\http\Request                                  $request         Request instance
	 * @param \mako\http\Response                                 $response        Response instance
	 * @param \mako\session\Session                               $session         Session instance
	 * @param array                                               $options         Options
	 */
	public function __construct(UserRepository $userRepository, GroupRepository $groupRepository, Request $request, Response $response, HttpSession $session, array $options = [])
	{
		$this->setUserRepository($userRepository);

		$this->setGroupRepository($groupRepository);

		$this->request = $request;

		$this->response = $response;

		$this->session = $session;

		$this->options = array_replace_recursive($this->options, $options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return 'session';
	}

	/**
	 * {@inheritdoc}
	 */
	public function createUser(string $email, string $username, string $password, bool $activate = false, array $properties = []): User
	{
		$properties = $properties +
		[
			'ip' => $this->request->ip(),
		];

		return parent::createUser($email, $username, $password, $activate, $properties);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUser()
	{
		if(empty($this->user))
		{
			// Check if there'a user that can be logged in

			$token = $this->session->get($this->options['auth_key'], false);

			if($token === false)
			{
				$token = $this->request->cookies->getSigned($this->options['auth_key'], false);

				if($token !== false)
				{
					$this->session->put($this->options['auth_key'], $token);
				}
			}

			if($token !== false)
			{
				$user = $this->userRepository->getByAccessToken($token);

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
	 * Returns TRUE if the email + password combination matches and the user is activated and not banned.
	 * A status code (LOGIN_ACTIVATING, LOGIN_BANNED or LOGIN_INCORRECT) will be retured in all other situations.
	 *
	 * @access protected
	 * @param  string   $identifier User email or username
	 * @param  string   $password   User password
	 * @param  bool     $force      Skip the password check?
	 * @return bool|int
	 */
	protected function authenticate($identifier, $password, $force = false)
	{
		$user = $this->userRepository->getByIdentifier($identifier);

		if($user !== false)
		{
			if($this->options['throttling']['enabled'] && $user->isLocked())
			{
				return Authentication::LOGIN_LOCKED;
			}

			if($force || $user->validatePassword($password))
			{
				if(!$user->isActivated())
				{
					return Authentication::LOGIN_ACTIVATING;
				}

				if($user->isBanned())
				{
					return Authentication::LOGIN_BANNED;
				}

				if($this->options['throttling']['enabled'])
				{
					$user->resetThrottle();
				}

				$this->user = $user;

				return true;
			}
			else
			{
				if($this->options['throttling']['enabled'])
				{
					$user->throttle($this->options['throttling']['max_attempts'], $this->options['throttling']['lock_time']);
				}
			}
		}

		return Authentication::LOGIN_INCORRECT;
	}

	/**
	 * Logs in a user with a valid identifier/password combination.
	 * Returns true if the identifier + password combination matches and the user is activated and not banned.
	 * A status code will be retured in all other situations.
	 *
	 * @access public
	 * @param  string   $identifier User identifier
	 * @param  string   $password   User password
	 * @param  bool     $remember   Set a remember me cookie?
	 * @param  bool     $force      Login the user without checking the password?
	 * @return bool|int
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

			$this->session->put($this->options['auth_key'], $this->user->getAccessToken());

			if($remember === true)
			{
				$this->response->signedCookie($this->options['auth_key'], $this->user->getAccessToken(), (3600 * 24 * 365), $this->options['cookie_options']);
			}

			return true;
		}

		return $authenticated;
	}

	/**
	 * Login a user without checking the password.
	 *
	 * @access public
	 * @param  mixed $identifier User email or username
	 * @param  bool  $remember   Set a remember me cookie?
	 * @return bool
	 */
	public function forceLogin($identifier, $remember = false): bool
	{
		return ($this->login($identifier, null, $remember, true) === true);
	}

	/**
	 * Builds and returns a basic HTTP authentication response.
	 *
	 * @access protected
	 * @return \mako\http\Response
	 */
	protected function basicHTTPAuthenticationResponse(): Response
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
	 * @access public
	 * @return \mako\http\Response|null
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
	 * @access public
	 */
	public function logout()
	{
		$this->session->regenerateId();

		$this->session->regenerateToken();

		$this->session->remove($this->options['auth_key']);

		$this->response->deleteCookie($this->options['auth_key'], $this->options['cookie_options']);

		$this->user = null;
	}
}
