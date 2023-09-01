<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\adapters;

use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\entities\user\UserEntityInterface;
use mako\gatekeeper\exceptions\GatekeeperException;
use mako\gatekeeper\Gatekeeper;
use mako\gatekeeper\repositories\group\GroupRepository;
use mako\gatekeeper\repositories\user\UserRepository;
use mako\http\Request;
use mako\http\Response;
use mako\session\Session as HttpSession;

use function array_replace_recursive;

/**
 * Session adapter.
 *
 * @method \mako\gatekeeper\entities\user\User|null getUser()
 */
class Session extends Adapter
{
	/**
	 * Adapter options.
	 */
	protected array $options =
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
	 * Has the user logged out?
	 */
	protected bool $hasLoggedOut = false;

	/**
	 * Constructor.
	 */
	public function __construct(
		UserRepository $userRepository,
		GroupRepository $groupRepository,
		protected Request $request,
		protected Response $response,
		protected HttpSession $session,
		array $options = []
	)
	{
		$this->setUserRepository($userRepository);

		$this->setGroupRepository($groupRepository);

		$this->options = array_replace_recursive($this->options, $options);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): string
	{
		return 'session';
	}

	/**
	 * {@inheritDoc}
	 */
	public function createUser(string $email, string $username, string $password, bool $activate = false, array $properties = []): User
	{
		$properties = $properties +
		[
			'ip' => $this->request->getIp(),
		];

		return parent::createUser($email, $username, $password, $activate, $properties);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUser(): ?UserEntityInterface
	{
		if($this->user === null && $this->hasLoggedOut === false)
		{
			// Check if there'a user that can be logged in

			$token = $this->session->get($this->options['auth_key']);

			if($token === null)
			{
				$token = $this->request->getCookies()->getSigned($this->options['auth_key']);

				if($token !== null)
				{
					$this->session->put($this->options['auth_key'], $token);
				}
			}

			if($token !== null)
			{
				$user = $this->userRepository->getByAccessToken($token);

				if($user === null || $user->isBanned() || !$user->isActivated())
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
	 * Returns TRUE if the identifier + password combination matches and the user is activated, not locked and not banned.
	 * A status code will be retured in all other situations.
	 *
	 * @return int|true
	 */
	protected function authenticate(int|string $identifier, ?string $password, bool $force = false)
	{
		$user = $this->userRepository->getByIdentifier($identifier);

		if($user !== null)
		{
			if($this->options['throttling']['enabled'] && $user->isLocked())
			{
				return Gatekeeper::LOGIN_LOCKED;
			}

			if($force || $user->validatePassword($password))
			{
				if(!$user->isActivated())
				{
					return Gatekeeper::LOGIN_ACTIVATING;
				}

				if($user->isBanned())
				{
					return Gatekeeper::LOGIN_BANNED;
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

		return Gatekeeper::LOGIN_INCORRECT;
	}

	/**
	 * Sets a remember me cookie.
	 */
	protected function setRememberMeCookie(): void
	{
		if($this->options['cookie_options']['secure'] && !$this->request->isSecure())
		{
			throw new GatekeeperException('Attempted to set a secure cookie over a non-secure connection.');
		}

		$this->response->getCookies()->addSigned($this->options['auth_key'], $this->user->getAccessToken(), (3600 * 24 * 365), $this->options['cookie_options']);
	}

	/**
	 * Logs in a user with a valid identifier/password combination.
	 * Returns TRUE if the identifier + password combination matches and the user is activated, not locked and not banned.
	 * A status code will be retured in all other situations.
	 *
	 * @return int|true
	 */
	public function login(int|string|null $identifier, ?string $password, bool $remember = false, bool $force = false)
	{
		if(empty($identifier))
		{
			return Gatekeeper::LOGIN_INCORRECT;
		}

		$authenticated = $this->authenticate($identifier, $password, $force);

		if($authenticated === true)
		{
			$this->session->regenerateId();

			$this->session->regenerateToken();

			$this->session->put($this->options['auth_key'], $this->user->getAccessToken());

			if($remember === true)
			{
				$this->setRememberMeCookie();
			}

			return true;
		}

		return $authenticated;
	}

	/**
	 * Login a user without checking the password.
	 * Returns TRUE if the identifier exists and the user is activated, not locked and not banned.
	 * A status code will be retured in all other situations.
	 *
	 * @return int|true
	 */
	public function forceLogin(int|string $identifier, bool $remember = false)
	{
		return $this->login($identifier, null, $remember, true);
	}

	/**
	 * Returns a basic authentication response if login is required and null if not.
	 */
	public function basicAuth(bool $clearResponse = false): bool
	{
		if($this->isLoggedIn() || $this->login($this->request->getUsername(), $this->request->getPassword()) === true)
		{
			return true;
		}

		if($clearResponse)
		{
			$this->response->clear();
		}

		$this->response->getHeaders()->add('WWW-Authenticate', 'basic');

		$this->response->setStatus(401);

		return false;
	}

	/**
	 * Logs the user out.
	 */
	public function logout(): void
	{
		$this->session->regenerateId();

		$this->session->regenerateToken();

		$this->session->remove($this->options['auth_key']);

		$this->response->getCookies()->delete($this->options['auth_key'], $this->options['cookie_options']);

		$this->user = null;

		$this->hasLoggedOut = true;
	}
}
