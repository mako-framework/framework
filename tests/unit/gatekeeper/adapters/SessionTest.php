<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\adapters;

use mako\gatekeeper\adapters\Session;
use mako\gatekeeper\Authentication;
use mako\gatekeeper\entities\group\Group;
use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\repositories\group\GroupRepository;
use mako\gatekeeper\repositories\user\UserRepository;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\Cookies as ResponseCookies;
use mako\http\response\Headers as ResponseHeaders;
use mako\session\Session as HttpSession;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class SessionTest extends TestCase
{
	/**
	 *
	 */
	public function getRequest()
	{
		return Mockery::mock(Request::class);
	}

	/**
	 *
	 */
	public function getResponse()
	{
		return Mockery::mock(Response::class);
	}

	/**
	 *
	 */
	public function getSession()
	{
		$session = Mockery::mock(HttpSession::class);

		return $session;
	}

	/**
	 *
	 */
	public function getUserRepository()
	{
		return Mockery::mock(UserRepository::class);
	}

	/**
	 *
	 */
	public function getUser()
	{
		return Mockery::mock(User::class);
	}

	/**
	 *
	 */
	public function getGroupRepository()
	{
		return Mockery::mock(GroupRepository::class);
	}

	/**
	 *
	 */
	public function getGroup()
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
	public function testGetUserRepository()
	{
		$adapter = new Session($this->getUserRepository(), $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertInstanceOf(UserRepository::class, $adapter->getUserRepository());
	}

	/**
	 *
	 */
	public function testGetGroupProvider()
	{
		$adapter = new Session($this->getUserRepository(), $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertInstanceOf(GroupRepository::class, $adapter->getGroupRepository());
	}

	/**
	 *
	 */
	public function testCreateUser()
	{
		$request = $this->getRequest();

		$request->shouldReceive('ip')->once()->andReturn('127.0.0.1');

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('createUser')->once()->with(['ip' => '127.0.0.1', 'email' => 'foo@example.org', 'username' => 'foo', 'password' => 'password', 'activated' => 0])->andReturn($this->getUser());

		$adapter = new Session($userRepository, $this->getGroupRepository(), $request, $this->getResponse(), $this->getSession());

		$this->assertInstanceOf(User::class, $adapter->createUser('foo@example.org', 'foo', 'password'));
	}

	/**
	 *
	 */
	public function testCreateAndActivateUser()
	{
		$request = $this->getRequest();

		$request->shouldReceive('ip')->once()->andReturn('127.0.0.1');

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('createUser')->once()->with(['ip' => '127.0.0.1', 'email' => 'foo@example.org', 'username' => 'foo', 'password' => 'password', 'activated' => 1])->andReturn($this->getUser());

		$adapter = new Session($userRepository, $this->getGroupRepository(), $request, $this->getResponse(), $this->getSession());

		$this->assertInstanceOf(User::class, $adapter->createUser('foo@example.org', 'foo', 'password', true));
	}

	/**
	 *
	 */
	public function testCreateGroup()
	{
		$groupRepository = $this->getGroupRepository();

		$groupRepository->shouldReceive('createGroup')->once()->with(['name' => 'foobar'])->andReturn($this->getGroup());

		$adapter = new Session($this->getUserRepository(), $groupRepository, $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertInstanceOf(Group::class, $adapter->createGroup('foobar'));
	}

	/**
	 *
	 */
	public function testActivateUserWithInvalidToken()
	{
		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByActionToken')->once()->with('foobar')->andReturn(false);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertFalse($adapter->activateUser('foobar'));
	}

	/**
	 *
	 */
	public function testActivateUserWithValidToken()
	{
		$user = $this->getUser();

		$user->shouldReceive('activate')->once();

		$user->shouldReceive('generateActionToken')->once();

		$user->shouldReceive('save')->once();

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByActionToken')->once()->with('foobar')->andReturn($user);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertInstanceOf(User::class, $adapter->activateUser('foobar'));
	}

	/**
	 *
	 */
	public function testAutenticationWithNoSessionAndNoCookie()
	{
		$session = $this->getSession();

		$session->shouldReceive('get')->times(3)->with('gatekeeper_auth_key', false)->andReturn(false);

		$request = $this->getRequest();

		$cookies = Mockery::mock('\mako\http\request\Cookies');

		$cookies->shouldReceive('getSigned')->times(3)->with('gatekeeper_auth_key', false)->andReturn(false);

		$request->shouldReceive('getCookies')->times(3)->andReturn($cookies);

		$adapter = new Session($this->getUserRepository(), $this->getGroupRepository(), $request, $this->getResponse(), $session);

		$this->assertTrue($adapter->isGuest());

		$this->assertFalse($adapter->isLoggedIn());

		$this->assertNull($adapter->getUser());
	}

	/**
	 *
	 */
	public function testAthenticationWithNoSessionAndInvalidToken()
	{
		$session = $this->getSession();

		$session->shouldReceive('get')->times(1)->with('gatekeeper_auth_key', false)->andReturn(false);

		$session->shouldReceive('put')->times(1)->with('gatekeeper_auth_key', 'token');

		$session->shouldReceive('regenerateId')->times(1);

		$session->shouldReceive('regenerateToken')->times(1);

		$session->shouldReceive('remove')->times(1)->with('gatekeeper_auth_key');

		$request = $this->getRequest();

		$cookies = Mockery::mock('\mako\http\request\Cookies');

		$cookies->shouldReceive('getSigned')->times(1)->with('gatekeeper_auth_key', false)->andReturn('token');

		$request->shouldReceive('getCookies')->times(1)->andReturn($cookies);

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByAccessToken')->times(1)->with('token')->andReturn(false);

		$response = $this->getResponse();

		$responseCookies = Mockery::mock(ResponseCookies::class);

		$responseCookies->shouldReceive('delete')->once()->with('gatekeeper_auth_key', $this->getCookieOptions());

		$response->shouldReceive('getCookies')->once()->andReturn($responseCookies);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $request, $response, $session);

		$this->assertTrue($adapter->isGuest());

		$this->assertFalse($adapter->isLoggedIn());

		$this->assertNull($adapter->getUser());
	}

	/**
	 *
	 */
	public function testAuthentication()
	{
		$session = $this->getSession();

		$session->shouldReceive('get')->once()->with('gatekeeper_auth_key', false)->andReturn('token');

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
	public function testLoginWithWrongEmail()
	{
		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn(false);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertEquals(Authentication::LOGIN_INCORRECT, $adapter->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */
	public function testLoginWithWrongPassword()
	{
		$userRepository = $this->getUserRepository();

		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(false);

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertEquals(Authentication::LOGIN_INCORRECT, $adapter->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */
	public function testLoginForNonActivatedUser()
	{
		$userRepository = $this->getUserRepository();

		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(true);

		$user->shouldReceive('isActivated')->once()->andReturn(false);

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertEquals(Authentication::LOGIN_ACTIVATING, $adapter->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */
	public function testLoginForBannedUser()
	{
		$userRepository = $this->getUserRepository();

		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(true);

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(true);

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$this->assertEquals(Authentication::LOGIN_BANNED, $adapter->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */
	public function testSuccessfulLogin()
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

		$this->assertTrue($adapter->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */
	public function testSuccessfulLoginWithRememberMe()
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

		$response->shouldReceive('getCookies')->once()->andReturn($responseCookies);

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $response, $session);

		$this->assertTrue($adapter->login('foo@example.org', 'password', true));
	}

	/**
	 *
	 */
	public function testForcedLogin()
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

		$this->assertTrue($adapter->forceLogin('foo@example.org'));
	}

	/**
	 *
	 */
	public function testLoginWithWrongPasswordAndThrottling()
	{
		$user = $this->getUser();

		$user->shouldReceive('validatePassword')->once()->with('password')->andReturn(false);

		$user->shouldReceive('isLocked')->once()->andReturn(false);

		$user->shouldReceive('throttle')->once()->with(5, 300);

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$options = ['throttling' => ['enabled' => true, 'max_attempts' => 5, 'lock_time' => 300]];

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession(), $options);

		$this->assertEquals(Authentication::LOGIN_INCORRECT, $adapter->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */
	public function testSuccessfulLoginWithThrottling()
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

		$this->assertTrue($adapter->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */
	public function testLoginWithLockedAccount()
	{
		$user = $this->getUser();

		$user->shouldReceive('isLocked')->once()->andReturn(true);

		$userRepository = $this->getUserRepository();

		$userRepository->shouldReceive('getByIdentifier')->once()->with('foo@example.org')->andReturn($user);

		$options = ['throttling' => ['enabled' => true, 'max_attempts' => 5, 'lock_time' => 300]];

		$adapter = new Session($userRepository, $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession(), $options);

		$this->assertEquals(Authentication::LOGIN_LOCKED, $adapter->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */
	public function testBasicAuth()
	{
		$request = $this->getRequest();

		$request->shouldReceive('username')->once()->andReturn(null);

		$request->shouldReceive('password')->once()->andReturn(null);

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->once()->with('WWW-Authenticate', 'basic');

		$response = $this->getResponse();

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('status')->once()->with(401);

		$adapter = Mockery::mock(Session::class . '[isLoggedIn,login]', [$this->getUserRepository(), $this->getGroupRepository(), $request, $response, $this->getSession()])->makePartial();

		$adapter->shouldReceive('isLoggedIn')->once()->andReturn(false);

		$adapter->shouldReceive('login')->once()->with(null, null)->andReturn(false);

		$this->assertFalse($adapter->basicAuth());
	}

	/**
	 *
	 */
	public function testBasicAuthWithClear()
	{
		$request = $this->getRequest();

		$request->shouldReceive('username')->once()->andReturn(null);

		$request->shouldReceive('password')->once()->andReturn(null);

		$responseHeaders = Mockery::mock(ResponseHeaders::class);

		$responseHeaders->shouldReceive('add')->once()->with('WWW-Authenticate', 'basic');

		$response = $this->getResponse();

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('status')->once()->with(401);

		$response->shouldReceive('clear')->once();

		$adapter = Mockery::mock(Session::class . '[isLoggedIn,login]', [$this->getUserRepository(), $this->getGroupRepository(), $request, $response, $this->getSession()])->makePartial();

		$adapter->shouldReceive('isLoggedIn')->once()->andReturn(false);

		$adapter->shouldReceive('login')->once()->with(null, null)->andReturn(false);

		$this->assertFalse($adapter->basicAuth(true));
	}

	/**
	 *
	 */
	public function testBasicAuthIsLoggedIn()
	{
		$adapter = Mockery::mock(Session::class . '[isLoggedIn]', [$this->getUserRepository(), $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession()])->makePartial();

		$adapter->shouldReceive('isLoggedIn')->once()->andReturn(true);

		$this->assertTrue($adapter->basicAuth());
	}

	/**
	 *
	 */
	public function testBasicAuthLoggingIn()
	{
		$request = $this->getRequest();

		$request->shouldReceive('username')->once()->andReturn('foo@example.org');

		$request->shouldReceive('password')->once()->andReturn('password');

		$adapter = Mockery::mock(Session::class . '[isLoggedIn,login]', [$this->getUserRepository(), $this->getGroupRepository(), $request, $this->getResponse(), $this->getSession()])->makePartial();

		$adapter->shouldReceive('isLoggedIn')->once()->andReturn(false);

		$adapter->shouldReceive('login')->once()->with('foo@example.org', 'password')->andReturn(true);

		$this->assertTrue($adapter->basicAuth());
	}

	/**
	 *
	 */
	public function testLogout()
	{
		$session = $this->getSession();

		$session->shouldReceive('regenerateId')->once();

		$session->shouldReceive('regenerateToken')->once();

		$session->shouldReceive('remove')->once()->with('gatekeeper_auth_key');

		$responseCookies = Mockery::mock(ResponseCookies::class);

		$responseCookies->shouldReceive('delete')->once()->with('gatekeeper_auth_key', $this->getCookieOptions());

		$response = $this->getResponse();

		$response->shouldReceive('getCookies')->once()->andReturn($responseCookies);

		$adapter = new Session($this->getUserRepository(), $this->getGroupRepository(), $this->getRequest(), $response, $session);

		$adapter->logout();
	}

	/**
	 *
	 */
	public function testSetUser()
	{
		$adapter = new Session($this->getUserRepository(), $this->getGroupRepository(), $this->getRequest(), $this->getResponse(), $this->getSession());

		$user = $this->getUser();

		$adapter->setUser($user);

		$this->assertEquals($user, $adapter->getUser());
	}
}
