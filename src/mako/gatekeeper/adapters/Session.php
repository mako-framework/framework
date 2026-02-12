<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\adapters;

use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\entities\user\UserEntityInterface;
use mako\gatekeeper\exceptions\GatekeeperException;
use mako\gatekeeper\LoginStatus;
use mako\gatekeeper\repositories\group\GroupRepository;
use mako\gatekeeper\repositories\user\UserRepository;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\Status;
use mako\session\Session as HttpSession;
use Override;
use SensitiveParameter;

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
	protected array $options = [
		'auth_key'       => 'gatekeeper_auth_key',
		'cookie_options' => [
			'path'        => '/',
			'domain'      => '',
			'secure'      => false,
			'partitioned' => false,
			'httponly'    => true,
		],
		'throttling'     => [
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
	) {
		$this->setUserRepository($userRepository);

		$this->setGroupRepository($groupRepository);

		$this->options = array_replace_recursive($this->options, $options);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getName(): string
	{
		return 'session';
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function createUser(string $email, string $username, #[SensitiveParameter] string $password, bool $activate = false, array $properties = []): User
	{
		$properties = $properties + [
			'ip' => $this->request->getIp(),
		];

		return parent::createUser($email, $username, $password, $activate, $properties);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getUser(): ?UserEntityInterface
	{
		if ($this->user === null && $this->hasLoggedOut === false) {
			// Check if there'a user that can be logged in

			$token = $this->session->get("mako:{$this->options['auth_key']}");

			if ($token === null) {
				$token = $this->request->cookies->getSigned($this->options['auth_key']);

				if ($token !== null) {
					$this->session->put("mako:{$this->options['auth_key']}", $token);
				}
			}

			if ($token !== null) {
				$user = $this->userRepository->getByAccessToken($token);

				if ($user === null || $user->isBanned() || !$user->isActivated()) {
					$this->logout();
				}
				else {
					$this->user = $user;
				}
			}
		}

		return $this->user;
	}

	/**
	 * Authenticates a user with a valid identifier/password combination.
	 */
	protected function authenticate(int|string $identifier, #[SensitiveParameter] ?string $password, bool $force = false): LoginStatus
	{
		$user = $this->userRepository->getByIdentifier($identifier);

		if ($user !== null) {
			if ($this->options['throttling']['enabled'] && $user->isLocked()) {
				return LoginStatus::LOCKED;
			}

			if ($force || $user->validatePassword($password)) {
				if (!$user->isActivated()) {
					return LoginStatus::NOT_ACTIVATED;
				}

				if ($user->isBanned()) {
					return LoginStatus::BANNED;
				}

				if ($this->options['throttling']['enabled']) {
					$user->resetThrottle();
				}

				$this->user = $user;

				return LoginStatus::OK;
			}
			else {
				if ($this->options['throttling']['enabled']) {
					$user->throttle($this->options['throttling']['max_attempts'], $this->options['throttling']['lock_time']);
				}
			}
		}

		return LoginStatus::INVALID_CREDENTIALS;
	}

	/**
	 * Sets a remember me cookie.
	 */
	protected function setRememberMeCookie(): void
	{
		if ($this->options['cookie_options']['secure'] && !$this->request->isSecure()) {
			throw new GatekeeperException('Attempted to set a secure cookie over a non-secure connection.');
		}

		$this->response->cookies->addSigned($this->options['auth_key'], $this->user->getAccessToken(), (3600 * 24 * 365), $this->options['cookie_options']);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function login(null|int|string $identifier, #[SensitiveParameter] ?string $password, bool $remember = false, bool $force = false): LoginStatus
	{
		if (empty($identifier)) {
			return LoginStatus::INVALID_CREDENTIALS;
		}

		$status = $this->authenticate($identifier, $password, $force);

		if ($status === LoginStatus::OK) {
			$this->session->regenerateId();

			$this->session->regenerateToken();

			$this->session->put("mako:{$this->options['auth_key']}", $this->user->getAccessToken());

			if ($remember === true) {
				$this->setRememberMeCookie();
			}
		}

		return $status;
	}

	/**
	 * Login a user without checking the password.
	 */
	public function forceLogin(int|string $identifier, bool $remember = false): LoginStatus
	{
		return $this->login($identifier, null, $remember, true);
	}

	/**
	 * Returns a basic authentication response if login is required and null if not.
	 */
	public function basicAuth(bool $clearResponse = false): bool
	{
		if ($this->isLoggedIn() || $this->login($this->request->getUsername(), $this->request->getPassword()) === LoginStatus::OK) {
			return true;
		}

		if ($clearResponse) {
			$this->response->clear();
		}

		$this->response->headers->add('WWW-Authenticate', 'basic');

		$this->response->setStatus(Status::UNAUTHORIZED);

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function logout(): void
	{
		$this->session->regenerateId();

		$this->session->regenerateToken();

		$this->session->remove("mako:{$this->options['auth_key']}");

		$this->response->cookies->delete($this->options['auth_key'], $this->options['cookie_options']);

		$this->user = null;

		$this->hasLoggedOut = true;
	}
}
