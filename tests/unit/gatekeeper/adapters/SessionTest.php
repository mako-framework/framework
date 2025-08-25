<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\adapters;

use mako\gatekeeper\adapters\Session;
use mako\gatekeeper\entities\group\Group;
use mako\gatekeeper\entities\group\GroupEntityInterface;
use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\entities\user\UserEntityInterface;
use mako\gatekeeper\exceptions\GatekeeperException;
use mako\gatekeeper\LoginStatus;
use mako\gatekeeper\repositories\group\GroupRepository;
use mako\gatekeeper\repositories\group\GroupRepositoryInterface;
use mako\gatekeeper\repositories\user\UserRepository;
use mako\gatekeeper\repositories\user\UserRepositoryInterface;
use mako\http\Request;
use mako\http\request\Cookies as RequestCookies;
use mako\http\Response;
use mako\http\response\Cookies as ResponseCookies;
use mako\http\response\Headers as ResponseHeaders;
use mako\http\response\Status;
use mako\session\Session as HttpSession;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group as GroupAttribute;

#[GroupAttribute('unit')]
class SessionTest extends TestCase
{
	/**
	 *
	 */
	public function getRequest(): MockInterface&Request
	{
		return Mockery::mock(Request::class);
	}

	/**
	 *
	 */
	public function getResponse(): MockInterface&Response
	{
		return Mockery::mock(Response::class);
	}

	/**
	 *
	 */
	public function getSession(): HttpSession&MockInterface
	{
		return Mockery::mock(HttpSession::class);
	}

	/**
	 *
	 */
	public function getUserRepository(): MockInterface&UserRepositoryInterface
	{
		return Mockery::mock(UserRepository::class);
	}

	/**
	 *
	 */
	public function getUser(): MockInterface&UserEntityInterface
	{
		return Mockery::mock(User::class);
	}

	/**
	 *
	 */
	public function getGroupRepository(): GroupRepositoryInterface&MockInterface
	{
		return Mockery::mock(GroupRepository::class);
	}

	/**
	 *
	 */
	public function getGroup(): GroupEntityInterface&MockInterface
	{
		return Mockery::mock(Group::class);
	}

	/**
	 *
	 */
	public function getCookieOptions()
	{
		return
		[
			'path'     => '/',
			'domain'   => '',
			'secure'   => false,
			'httponly' => true,
		];
	}

	/**
	 *
	 */
	public function testGetUserRepository(): void
	{
		$adapter = new Session($this->getUserRepository(), $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertInstanceOf(UserRepository::class, $adapter->getUserRepository());
	}

	/**
	 *
	 */
	public function testGetGroupProvider(): void
	{
		$adapter = new Session($this->getUserRepository(), $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertInstanceOf(GroupRepository::class, $adapter->getGroupRepository());
	}

	/**
	 *
	 */
	public function testCreateUser(): void
	{
		$request = $this->getRequest();

		$request->shouldReceive('getIp')->once()->andReturn('127.0.0.1');

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('createUser')->once()->with(['ip' => '127.0.0.1', 'email' => 'foo@example.org', 'username' => 'foo', 'password' => 'password', 'activated' => 0])->andReturn($this->getUser());

		$adapter = new Session($userRepository, $this->getGroupRepository(), $request, $this->getResponse(), $this->getSession());

		$this->assertInstanceOf(User::class, $adapter->createUser('foo@example.org', 'foo', 'password'));
	}

	/**
	 *
	 */
	public function testCreateAndActivateUser(): void
	{
		$request = $this->getRequest();

		$request->shouldReceive('getIp')->once()->andReturn('127.0.0.1');

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('createUser')->once()->with(['ip' => '127.0.0.1', 'email' => 'foo@example.org', 'username' => 'foo', 'password' => 'password', 'activated' => 1])->andReturn($this->getUser());

		$adapter = new Session($userRepository, $this->getGroupRepository(), $request, $this->getResponse(), $this->getSession());

		$this->assertInstanceOf(User::class, $adapter->createUser('foo@example.org', 'foo', 'password', true));
	}

	/**
	 *
	 */
	public function testCreateGroup(): void
	{
		$groupRepository = $this->getGroupRepository();

		$groupRepository->shouldReceive('createGroup')->once()->with(['name' => 'foobar'])->andReturn($this->getGroup());

		$adapter = new Session($this->getUserRepository(), $groupRepository, $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertInstanceOf(Group::class, $adapter->createGroup('foobar'));
	}

	/**
	 *
	 */
	public function testActivateUserWithInvalidToken(): void
	{
		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByActionToken')->once()->with('foobar')->andReturn(null);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertFalse($adapter->activateUser('foobar'));
	}

	/**
	 *
	 */
	public function testActivateUserWithValidToken(): void
	{
		$user = $this->getUser();

		$user->shouldReceive('activate')->once();

		$user->shouldReceive('generateActionToken')->once();

		$user->shouldReceive('save')->once();

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByActionToken')->once()->with('foobar')->andReturn($user);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertTrue($adapter->activateUser('foobar'));
	}

	/**
	 *
	 */
	public function testAutenticationWithNoSessionAndNoCookie(): void
	{
		$session = $this->getSession();

		$session->shouldReceive('get')->times(3)->with('gatekeeper_auth_key')->andReturn(null);

		$request = $this->getRequest();

		$cookies = Mockery::mock(RequestCookies::class);

		$cookies->shouldReceive('getSigned')->times(3)->with('gatekeeper_auth_key')->andReturn(null);

		(function () use ($cookies): void {
			$this->cookies = $cookies;
		})->bindTo($request, Request::class)();

		$adapter = new Session($this->getUserRepository(), $this->getGroupRepository(), $request, $this->getResponse(), $session);

		$this->assertTrue($adapter->isGuest());

		$this->assertFalse($adapter->isLoggedIn());

		$this->assertNull($adapter->getUser());
	}

	/**
	 *
	 */
	public function testAthenticationWithNoSessionAndInvalidToken(): void
	{
		$session = $this->getSession();

		$session->shouldReceive('get')->times(1)->with('gatekeeper_auth_key')->andReturn(null);

		$session->shouldReceive('put')->times(1)->with('gatekeeper_auth_key', 'token');

		$session->shouldReceive('regenerateId')->times(1);

		$session->shouldReceive('regenerateToken')->times(1);

		$session->shouldReceive('remove')->times(1)->with('gatekeeper_auth_key');

		$request = $this->getRequest();

		$cookies = Mockery::mock(RequestCookies::class);

		$cookies->shouldReceive('getSigned')->times(1)->with('gatekeeper_auth_key')->andReturn('token');

		(function () use ($cookies): void {
			$this->cookies = $cookies;
		})->bindTo($request, Request::class)();

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByAccessToken')->times(1)->with('token')->andReturn(null);

		$response = $this->getResponse();

		$responseCookies = Mockery::mock(ResponseCookies::class);

		$responseCookies->shouldReceive('delete')->once()->with('gatekeeper_auth_key', $this->getCookieOptions());

		(function () use ($responseCookies): void {
			$this->cookies = $responseCookies;
		})->bindTo($response, Response::class)();

		$adapter = new Session($userRepository, $this->getGroupRepository(), $request, $response, $session);

		$this->assertTrue($adapter->isGuest());

		$this->assertFalse($adapter->isLoggedIn());

		$this->assertNull($adapter->getUser());
	}

	/**
	 *
	 */
	public function testAuthentication(): void
	{
		$session = $this->getSession();

		$session->shouldReceive('get')->once()->with('gatekeeper_auth_key')->andReturn('token');

		$user = $this->getUser();

		$user->shouldReceive('isBanned')->once()->andReturn(false);

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByAccessToken')->once()->with('token')->andReturn($user);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $session);

		$this->assertFalse($adapter->isGuest());

		$this->assertTrue($adapter->isLoggedIn());

		$this->assertInstanceOf(User::class, $adapter->getUser());
	}

	/**
	 *
	 */
	public function testLoginWithWrongEmail(): void
	{
		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn(null);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$status = $adapter->login('foo@example.org', 'password');

		$this->assertFalse($status->toBool());
		$this->assertEquals(LoginStatus::INVALID_CREDENTIALS, $status);
	}

	/**
	 *
	 */
	public function testLoginWithWrongPassword(): void
	{
		$userRepository = $this->getUserRepository();

		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(false);

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$status = $adapter->login('foo@example.org', 'password');

		$this->assertFalse($status->toBool());
		$this->assertEquals(LoginStatus::INVALID_CREDENTIALS, $status);
	}

	/**
	 *
	 */
	public function testLoginForNonActivatedUser(): void
	{
		$userRepository = $this->getUserRepository();

		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(true);

		$user->shouldReceive('isActivated')->once()->andReturn(false);

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$status = $adapter->login('foo@example.org', 'password');

		$this->assertFalse($status->toBool());
		$this->assertEquals(LoginStatus::NOT_ACTIVATED, $status);
	}

	/**
	 *
	 */
	public function testLoginForBannedUser(): void
	{
		$userRepository = $this->getUserRepository();

		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(true);

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(true);

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$status = $adapter->login('foo@example.org', 'password');

		$this->assertFalse($status->toBool());
		$this->assertEquals(LoginStatus::BANNED, $status);
	}

	/**
	 *
	 */
	public function testSuccessfulLogin(): void
	{
		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(true);

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(false);

		$user->shouldReceive('getAccessToken')->once()->andReturn('token');

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$session = $this->getSession();

		$session->shouldReceive('regenerateId')->once();

		$session->shouldReceive('regenerateToken')->once();

		$session->shouldReceive('put')->once()->with('gatekeeper_auth_key', 'token');

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $session);

		$status = $adapter->login('foo@example.org', 'password');

		$this->assertTrue($status->toBool());
		$this->assertEquals(LoginStatus::OK, $status);
	}

	/**
	 *
	 */
	public function testSuccessfulLoginWithRememberMe(): void
	{
		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(true);

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(false);

		$user->shouldReceive('getAccessToken')->twice()->andReturn('token');

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$session = $this->getSession();

		$session->shouldReceive('regenerateId')->once();

		$session->shouldReceive('regenerateToken')->once();

		$session->shouldReceive('put')->once()->with('gatekeeper_auth_key', 'token');

		$responseCookies = Mockery::mock(ResponseCookies::class);

		$responseCookies->shouldReceive('addSigned')->once()->with('gatekeeper_auth_key', 'token', 31536000, $this->getCookieOptions());

		$response = $this->getResponse();

		(function () use ($responseCookies): void {
			$this->cookies = $responseCookies;
		})->bindTo($response, Response::class)();

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $response, $session);

		$this->assertTrue($adapter->login('foo@example.org', 'password', true)->toBool());
	}

	/**
	 *
	 */
	public function testSuccessfulLoginWithRememberMeWithSecureCookieOverNonSecureConnection(): void
	{
		$this->expectException(GatekeeperException::class);

		$this->expectExceptionMessage('Attempted to set a secure cookie over a non-secure connection.');

		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(true);

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(false);

		$user->shouldReceive('getAccessToken')->once()->andReturn('token');

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$session = $this->getSession();

		$session->shouldReceive('regenerateId')->once();

		$session->shouldReceive('regenerateToken')->once();

		$session->shouldReceive('put')->once()->with('gatekeeper_auth_key', 'token');

		$request = $this->getRequest();

		$request->shouldReceive('isSecure')->once()->andReturn(false);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $request, $this->getResponse(), $session, ['cookie_options' => ['secure' => true]]);

		$this->assertTrue($adapter->login('foo@example.org', 'password', true)->toBool());
	}

	/**
	 *
	 */
	public function testSuccessfulLoginWithRememberMeWithSecureCookieOverSecureConnection(): void
	{
		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(true);

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(false);

		$user->shouldReceive('getAccessToken')->twice()->andReturn('token');

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$session = $this->getSession();

		$session->shouldReceive('regenerateId')->once();

		$session->shouldReceive('regenerateToken')->once();

		$session->shouldReceive('put')->once()->with('gatekeeper_auth_key', 'token');

		$request = $this->getRequest();

		$request->shouldReceive('isSecure')->once()->andReturn(true);

		$responseCookies = Mockery::mock(ResponseCookies::class);

		$responseCookies->shouldReceive('addSigned')->once()->with('gatekeeper_auth_key', 'token', 31536000, ['secure' => true] + $this->getCookieOptions());

		$response = $this->getResponse();

		(function () use ($responseCookies): void {
			$this->cookies = $responseCookies;
		})->bindTo($response, Response::class)();

		$adapter = new Session($userRepository, $this->getGroupRepository(), $request, $response, $session, ['cookie_options' => ['secure' => true]]);

		$this->assertTrue($adapter->login('foo@example.org', 'password', true)->toBool());
	}

	/**
	 *
	 */
	public function testForcedLogin(): void
	{
		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->never();

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(false);

		$user->shouldReceive('getAccessToken')->once()->andReturn('token');

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$session = $this->getSession();

		$session->shouldReceive('regenerateId')->once();

		$session->shouldReceive('regenerateToken')->once();

		$session->shouldReceive('put')->once()->with('gatekeeper_auth_key', 'token');

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $session);

		$this->assertTrue($adapter->forceLogin('foo@example.org')->toBool());
	}

	/**
	 *
	 */
	public function testLoginWithWrongPasswordAndThrottling(): void
	{
		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(false);

		$user->shouldReceive('isLocked')->once()->andReturn(false);

		$user->shouldReceive('throttle')->once()->with(5, 300);

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$options = ['throttling' => ['enabled' => true, 'max_attempts' => 5, 'lock_time' => 300]];

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession(), $options);

		$status = $adapter->login('foo@example.org', 'password');

		$this->assertFalse($status->toBool());
		$this->assertEquals(LoginStatus::INVALID_CREDENTIALS, $status);
	}

	/**
	 *
	 */
	public function testSuccessfulLoginWithThrottling(): void
	{
		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(true);

		$user->shouldReceive('isLocked')->once()->andReturn(false);

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(false);

		$user->shouldReceive('getAccessToken')->once()->andReturn('token');

		$user->shouldReceive('resetThrottle')->once();

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$session = $this->getSession();

		$session->shouldReceive('regenerateId')->once();

		$session->shouldReceive('regenerateToken')->once();

		$session->shouldReceive('put')->once()->with('gatekeeper_auth_key', 'token');

		$options = ['throttling' => ['enabled' => true, 'max_attempts' => 5, 'lock_time' => 300]];

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $session, $options);

		$this->assertTrue($adapter->login('foo@example.org', 'password')->toBool());
	}

	/**
	 *
	 */
	public function testLoginWithLockedAccount(): void
	{
		$user = $this->getUser();

		$user->shouldReceive('isLocked')->once()->andReturn(true);

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$options = ['throttling' => ['enabled' => true, 'max_attempts' => 5, 'lock_time' => 300]];

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession(), $options);

		$status = $adapter->login('foo@example.org', 'password');

		$this->assertFalse($status->toBool());
		$this->assertEquals(LoginStatus::LOCKED, $status);
	}

	/**
	 *
	 */
	public function testBasicAuth(): void
	{
		$request = $this->getRequest();

		$request->shouldReceive('getUsername')->once()->andReturn(null);

		$request->shouldReceive('getPassword')->once()->andReturn(null);

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->once()->with('WWW-Authenticate', 'basic');

		$response = $this->getResponse();

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('setStatus')->once()->with(Status::UNAUTHORIZED);

		$adapter = Mockery::mock(Session::class . '[isLoggedIn,login]', [$this->getUserRepository(), $this->getGroupRepository(), $request, $response, $this->getSession()]);

		/** @var MockInterface&Session $adapter */
		$adapter = $adapter->makePartial();

		$adapter->shouldReceive('isLoggedIn')->once()->andReturn(false);

		$adapter->shouldReceive('login')->once()->with(null, null)->andReturn(LoginStatus::INVALID_CREDENTIALS);

		$this->assertFalse($adapter->basicAuth());
	}

	/**
	 *
	 */
	public function testBasicAuthWithClear(): void
	{
		$request = $this->getRequest();

		$request->shouldReceive('getUsername')->once()->andReturn(null);

		$request->shouldReceive('getPassword')->once()->andReturn(null);

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->once()->with('WWW-Authenticate', 'basic');

		$response = $this->getResponse();

		(function () use ($responseHeaders): void {
			$this->headers = $responseHeaders;
		})->bindTo($response, Response::class)();

		$response->shouldReceive('setStatus')->once()->with(Status::UNAUTHORIZED);

		$response->shouldReceive('clear')->once();

		$adapter = Mockery::mock(Session::class . '[isLoggedIn,login]', [$this->getUserRepository(), $this->getGroupRepository(), $request, $response, $this->getSession()]);

		/** @var MockInterface&Session $adapter */
		$adapter = $adapter->makePartial();

		$adapter->shouldReceive('isLoggedIn')->once()->andReturn(false);

		$adapter->shouldReceive('login')->once()->with(null, null)->andReturn(LoginStatus::INVALID_CREDENTIALS);

		$this->assertFalse($adapter->basicAuth(true));
	}

	/**
	 *
	 */
	public function testBasicAuthIsLoggedIn(): void
	{
		$adapter = Mockery::mock(Session::class . '[isLoggedIn]', [$this->getUserRepository(), $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession()]);

		/** @var MockInterface&Session $adapter */
		$adapter = $adapter->makePartial();

		$adapter->shouldReceive('isLoggedIn')->once()->andReturn(true);

		$this->assertTrue($adapter->basicAuth());
	}

	/**
	 *
	 */
	public function testBasicAuthLoggingIn(): void
	{
		$request = $this->getRequest();

		$request->shouldReceive('getUsername')->once()->andReturn('foo@example.org');

		$request->shouldReceive('getPassword')->once()->andReturn('password');

		$adapter = Mockery::mock(Session::class . '[isLoggedIn,login]', [$this->getUserRepository(), $this->getGroupRepository(), $request, $this->getResponse(), $this->getSession()]);

		/** @var MockInterface&Session $adapter */
		$adapter = $adapter->makePartial();

		$adapter->shouldReceive('isLoggedIn')->once()->andReturn(false);

		$adapter->shouldReceive('login')->once()->with('foo@example.org', 'password')->andReturn(LoginStatus::OK);

		$this->assertTrue($adapter->basicAuth());
	}

	/**
	 *
	 */
	public function testLogout(): void
	{
		$session = $this->getSession();

		$session->shouldReceive('regenerateId')->once();

		$session->shouldReceive('regenerateToken')->once();

		$session->shouldReceive('remove')->once()->with('gatekeeper_auth_key');

		$responseCookies = Mockery::mock(ResponseCookies::class);

		$responseCookies->shouldReceive('delete')->once()->with('gatekeeper_auth_key', $this->getCookieOptions());

		$response = $this->getResponse();

		(function () use ($responseCookies): void {
			$this->cookies = $responseCookies;
		})->bindTo($response, Response::class)();

		$adapter = new Session($this->getUserRepository(), $this->getGroupRepository(), $this->getRequest(), $response, $session);

		$adapter->logout();
	}

	/**
	 *
	 */
	public function testSetUser(): void
	{
		$adapter = new Session($this->getUserRepository(), $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$user = $this->getUser();

		$adapter->setUser($user);

		$this->assertEquals($user, $adapter->getUser());
	}
}
