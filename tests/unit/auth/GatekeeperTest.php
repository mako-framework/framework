<?php

namespace mako\tests\unit\auth;

use mako\auth\Gatekeeper;

use \Mockery as m;

/**
 * @group unit
 */

class GatekeeperTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function getRequest()
	{
		return m::mock('\mako\http\Request');
	}

	/**
	 *
	 */

	public function getResponse()
	{
		return m::mock('\mako\http\Response');
	}

	/**
	 *
	 */

	public function getSession()
	{
		$store = m::mock('\mako\session\stores\StoreInterface');

		$store->shouldReceive('gc');

		$session = m::mock('\mako\session\Session', [$this->getRequest(), $this->getResponse(), $store]);

		return $session;
	}

	/**
	 *
	 */

	public function getUserProvider()
	{
		return m::mock('\mako\auth\providers\UserProvider');
	}

	/**
	 *
	 */

	public function getUser()
	{
		return m::mock('\mako\auth\user\UserInterface');
	}

	/**
	 *
	 */

	public function getGroupProvider()
	{
		return m::mock('\mako\auth\providers\GroupProvider');
	}

	/**
	 *
	 */

	public function getGroup()
	{
		return m::mock('\mako\auth\group\GroupInterface');
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

	public function testGetUserProvider()
	{
		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $this->getUserProvider(), $this->getGroupProvider());

		$this->assertInstanceOf('mako\auth\providers\UserProvider', $gatekeeper->getUserProvider());
	}

	/**
	 *
	 */

	public function testGetGroupProvider()
	{
		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $this->getUserProvider(), $this->getGroupProvider());

		$this->assertInstanceOf('mako\auth\providers\GroupProvider', $gatekeeper->getGroupProvider());
	}

	/**
	 *
	 */

	public function testCreateUser()
	{
		$request = $this->getRequest();

		$request->shouldReceive('ip')->once()->andReturn('127.0.0.1');

		$user = $this->getUser();

		$user->shouldReceive('generateActionToken')->once();

		$user->shouldReceive('generateAccessToken')->once();

		$user->shouldReceive('save')->once();

		$userProvider = $this->getUserProvider();

		$userProvider->shouldReceive('createUser')->once()->with('foo@example.org', 'foo', 'password', '127.0.0.1')->andReturn($user);

		$gatekeeper = new Gatekeeper($request, $this->getResponse(), $this->getSession(), $userProvider, $this->getGroupProvider());

		$this->assertInstanceOf('mako\auth\user\UserInterface', $gatekeeper->createUser('foo@example.org', 'foo', 'password'));
	}

	/**
	 *
	 */

	public function testCreateAndActivateUser()
	{
		$request = $this->getRequest();

		$request->shouldReceive('ip')->once()->andReturn('127.0.0.1');

		$user = $this->getUser();

		$user->shouldReceive('generateActionToken')->once();

		$user->shouldReceive('generateAccessToken')->once();

		$user->shouldReceive('activate')->once();

		$user->shouldReceive('save')->once();

		$userProvider = $this->getUserProvider();

		$userProvider->shouldReceive('createUser')->once()->with('foo@example.org', 'foo', 'password', '127.0.0.1')->andReturn($user);

		$gatekeeper = new Gatekeeper($request, $this->getResponse(), $this->getSession(), $userProvider, $this->getGroupProvider());

		$this->assertInstanceOf('mako\auth\user\UserInterface', $gatekeeper->createUser('foo@example.org', 'foo', 'password', true));
	}

	/**
	 *
	 */

	public function testCreateGroup()
	{
		$groupProvider = $this->getGroupProvider();

		$groupProvider->shouldReceive('createGroup')->once()->with('foobar')->andReturn($this->getGroup());

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $this->getUserProvider(), $groupProvider);

		$this->assertInstanceOf('mako\auth\group\GroupInterface', $gatekeeper->createGroup('foobar'));
	}

	/**
	 *
	 */

	public function testActivateUserWithInvalidToken()
	{
		$userProvider = $this->getUserProvider();

		$userProvider->shouldReceive('getByActionToken')->once()->with('foobar')->andReturn(false);

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $userProvider, $this->getGroupProvider());

		$this->assertFalse($gatekeeper->activateUser('foobar'));
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

		$userProvider = $this->getUserProvider();

		$userProvider->shouldReceive('getByActionToken')->once()->with('foobar')->andReturn($user);

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $userProvider, $this->getGroupProvider());

		$this->assertInstanceOf('mako\auth\user\UserInterface', $gatekeeper->activateUser('foobar'));
	}

	/**
	 *
	 */

	public function testAutenticationWithNoSessionAndNoCookie()
	{
		$session = $this->getSession();

		$session->shouldReceive('get')->times(3)->with('gatekeeper_auth_key', false)->andReturn(false);

		$request = $this->getRequest();

		$request->shouldReceive('signedCookie')->times(3)->with('gatekeeper_auth_key', false)->andReturn(false);

		$gatekeeper = new Gatekeeper($request, $this->getResponse(), $session, $this->getUserProvider(), $this->getGroupProvider());

		$this->assertTrue($gatekeeper->isGuest());

		$this->assertFalse($gatekeeper->isLoggedIn());

		$this->assertNull($gatekeeper->getUser());
	}

	/**
	 *
	 */

	public function testAthenticationWithNoSessionAndInvalidToken()
	{
		$session = $this->getSession();

		$session->shouldReceive('get')->times(3)->with('gatekeeper_auth_key', false)->andReturn(false);

		$session->shouldReceive('put')->times(3)->with('gatekeeper_auth_key', 'token');

		$session->shouldReceive('regenerateId')->times(3);

		$session->shouldReceive('regenerateToken')->times(3);

		$session->shouldReceive('remove')->times(3)->with('gatekeeper_auth_key');

		$request = $this->getRequest();

		$request->shouldReceive('signedCookie')->times(3)->with('gatekeeper_auth_key', false)->andReturn('token');

		$userProvider = $this->getUserProvider();

		$userProvider->shouldReceive('getByAccessToken')->times(3)->with('token')->andReturn(false);

		$response = $this->getResponse();

		$response->shouldReceive('deleteCookie')->times(3)->with('gatekeeper_auth_key', $this->getCookieOptions());

		$gatekeeper = new Gatekeeper($request, $response, $session, $userProvider, $this->getGroupProvider());

		$this->assertTrue($gatekeeper->isGuest());

		$this->assertFalse($gatekeeper->isLoggedIn());

		$this->assertNull($gatekeeper->getUser());
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

		$userProvider = $this->getUserProvider();

		$userProvider->shouldReceive('getByAccessToken')->once()->with('token')->andReturn($user);

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $session, $userProvider, $this->getGroupProvider());

		$this->assertFalse($gatekeeper->isGuest());

		$this->assertTrue($gatekeeper->isLoggedIn());

		$this->assertInstanceOf('mako\auth\user\UserInterface', $gatekeeper->getUser());
	}

	/**
	 *
	 */

	public function testLoginWithWrongEmail()
	{
		$userProvider = $this->getUserProvider();

		$userProvider->shouldReceive('getByEmail')->once()->with('foo@example.org')->andReturn(false);

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $userProvider, $this->getGroupProvider());

		$this->assertEquals(Gatekeeper::LOGIN_INCORRECT, $gatekeeper->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */

	public function testLoginWithWrongUsername()
	{
		$userProvider = $this->getUserProvider();

		$userProvider->shouldReceive('getByUsername')->once()->with('foo')->andReturn(false);

		$options = ['identifier' => 'username'];

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $userProvider, $this->getGroupProvider(), $options);

		$this->assertEquals(Gatekeeper::LOGIN_INCORRECT, $gatekeeper->login('foo', 'password'));
	}

	/**
	 * @expectedException \RuntimeException
	 */

	public function testLoginWithUnsupportedIdentifier()
	{
		$userProvider = $this->getUserProvider();

		$options = ['identifier' => 'foobar'];

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $userProvider, $this->getGroupProvider(), $options);

		$this->assertEquals(Gatekeeper::LOGIN_INCORRECT, $gatekeeper->login('foo', 'password'));
	}

	/**
	 *
	 */

	public function testLoginWithWrongPassword()
	{
		$userProvider = $this->getUserProvider();

		$user = $this->getUser();

		$userProvider->shouldReceive('getByEmail')->once()->with('foo@example.org')->andReturn($user);

		$userProvider->shouldReceive('validatePassword')->once()->andReturn(false);

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $userProvider, $this->getGroupProvider());

		$this->assertEquals(Gatekeeper::LOGIN_INCORRECT, $gatekeeper->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */

	public function testLoginForNonActivatedUser()
	{
		$userProvider = $this->getUserProvider();

		$user = $this->getUser();

		$user->shouldReceive('isActivated')->once()->andReturn(false);

		$userProvider->shouldReceive('getByEmail')->once()->with('foo@example.org')->andReturn($user);

		$userProvider->shouldReceive('validatePassword')->once()->andReturn(true);

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $userProvider, $this->getGroupProvider());

		$this->assertEquals(Gatekeeper::LOGIN_ACTIVATING, $gatekeeper->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */

	public function testLoginForBannedUser()
	{
		$userProvider = $this->getUserProvider();

		$user = $this->getUser();

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(true);

		$userProvider->shouldReceive('getByEmail')->once()->with('foo@example.org')->andReturn($user);

		$userProvider->shouldReceive('validatePassword')->once()->andReturn(true);

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $userProvider, $this->getGroupProvider());

		$this->assertEquals(Gatekeeper::LOGIN_BANNED, $gatekeeper->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */

	public function testSuccessfulLogin()
	{
		$userProvider = $this->getUserProvider();

		$user = $this->getUser();

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(false);

		$user->shouldReceive('getAccessToken')->once()->andReturn('token');

		$userProvider->shouldReceive('getByEmail')->once()->with('foo@example.org')->andReturn($user);

		$userProvider->shouldReceive('validatePassword')->once()->andReturn(true);

		$session = $this->getSession();

		$session->shouldReceive('regenerateId')->once();

		$session->shouldReceive('regenerateToken')->once();

		$session->shouldReceive('put')->once()->with('gatekeeper_auth_key', 'token');

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $session, $userProvider, $this->getGroupProvider());

		$this->assertTrue($gatekeeper->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */

	public function testSuccessfulLoginWithRememberMe()
	{
		$userProvider = $this->getUserProvider();

		$user = $this->getUser();

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(false);

		$user->shouldReceive('getAccessToken')->twice()->andReturn('token');

		$userProvider->shouldReceive('getByEmail')->once()->with('foo@example.org')->andReturn($user);

		$userProvider->shouldReceive('validatePassword')->once()->andReturn(true);

		$session = $this->getSession();

		$session->shouldReceive('regenerateId')->once();

		$session->shouldReceive('regenerateToken')->once();

		$session->shouldReceive('put')->once()->with('gatekeeper_auth_key', 'token');

		$response = $this->getResponse();

		$response->shouldReceive('signedCookie')->once()->with('gatekeeper_auth_key', 'token', 31536000, $this->getCookieOptions());

		$gatekeeper = new Gatekeeper($this->getRequest(), $response, $session, $userProvider, $this->getGroupProvider());

		$this->assertTrue($gatekeeper->login('foo@example.org', 'password', true));
	}

	/**
	 *
	 */

	public function testForcedLogin()
	{
		$userProvider = $this->getUserProvider();

		$user = $this->getUser();

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(false);

		$user->shouldReceive('getAccessToken')->once()->andReturn('token');

		$userProvider->shouldReceive('getByEmail')->once()->with('foo@example.org')->andReturn($user);

		$userProvider->shouldReceive('validatePassword')->once()->andReturn(false);

		$session = $this->getSession();

		$session->shouldReceive('regenerateId')->once();

		$session->shouldReceive('regenerateToken')->once();

		$session->shouldReceive('put')->once()->with('gatekeeper_auth_key', 'token');

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $session, $userProvider, $this->getGroupProvider());

		$this->assertTrue($gatekeeper->forceLogin('foo@example.org'));
	}

	/**
	 *
	 */

	public function testLoginWithWrongPasswordAndThrottling()
	{
		$userProvider = $this->getUserProvider();

		$user = $this->getUser();

		$user->shouldReceive('isLocked')->once()->andReturn(false);

		$userProvider->shouldReceive('getByEmail')->once()->with('foo@example.org')->andReturn($user);

		$userProvider->shouldReceive('validatePassword')->once()->andReturn(false);

		$userProvider->shouldReceive('throttle')->once()->with($user, 5, 300);

		$options = ['throttling' => ['enabled' => true, 'max_attempts' => 5, 'lock_time' => 300]];

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $userProvider, $this->getGroupProvider(), $options);

		$this->assertEquals(Gatekeeper::LOGIN_INCORRECT, $gatekeeper->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */

	public function testSuccessfulLoginWithThrottling()
	{
		$userProvider = $this->getUserProvider();

		$user = $this->getUser();

		$user->shouldReceive('isLocked')->once()->andReturn(false);

		$user->shouldReceive('isActivated')->once()->andReturn(true);

		$user->shouldReceive('isBanned')->once()->andReturn(false);

		$user->shouldReceive('getAccessToken')->once()->andReturn('token');

		$userProvider->shouldReceive('getByEmail')->once()->with('foo@example.org')->andReturn($user);

		$userProvider->shouldReceive('validatePassword')->once()->andReturn(true);

		$userProvider->shouldReceive('resetThrottle')->once()->with($user);

		$session = $this->getSession();

		$session->shouldReceive('regenerateId')->once();

		$session->shouldReceive('regenerateToken')->once();

		$session->shouldReceive('put')->once()->with('gatekeeper_auth_key', 'token');

		$options = ['throttling' => ['enabled' => true, 'max_attempts' => 5, 'lock_time' => 300]];

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $session, $userProvider, $this->getGroupProvider(), $options);

		$this->assertTrue($gatekeeper->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */

	public function testLoginWithLockedAccount()
	{
		$userProvider = $this->getUserProvider();

		$user = $this->getUser();

		$user->shouldReceive('isLocked')->once()->andReturn(true);

		$userProvider->shouldReceive('getByEmail')->once()->with('foo@example.org')->andReturn($user);

		$options = ['throttling' => ['enabled' => true, 'max_attempts' => 5, 'lock_time' => 300]];

		$gatekeeper = new Gatekeeper($this->getRequest(), $this->getResponse(), $this->getSession(), $userProvider, $this->getGroupProvider(), $options);

		$this->assertEquals(Gatekeeper::LOGIN_LOCKED, $gatekeeper->login('foo@example.org', 'password'));
	}

	/**
	 *
	 */

	public function testBasicAuth()
	{
		$request = $this->getRequest();

		$request->shouldReceive('username')->once()->andReturn(null);

		$request->shouldReceive('password')->once()->andReturn(null);

		$response = $this->getResponse();

		$gatekeeper = m::mock('\mako\auth\Gatekeeper', [$request, $response, $this->getSession(), $this->getUserProvider(), $this->getGroupProvider()])->makePartial();

		$gatekeeper->shouldReceive('isLoggedIn')->once()->andReturn(false);

		$gatekeeper->shouldReceive('login')->once()->with(null, null)->andReturn(false);

		$response = $gatekeeper->basicAuth();

		$this->assertInstanceOf('mako\http\Response', $response);

		$this->assertEquals(401, $response->getStatus());

		$this->assertEquals('Authentication required.', $response->getBody());

		$this->assertEquals(['www-authenticate' => ['basic']], $response->getHeaders());
	}

	/**
	 *
	 */

	public function testBasicAuthIsLoggedIn()
	{
		$gatekeeper = m::mock('\mako\auth\Gatekeeper', [$this->getRequest(), $this->getResponse(), $this->getSession(), $this->getUserProvider(), $this->getGroupProvider()])->makePartial();

		$gatekeeper->shouldReceive('isLoggedIn')->once()->andReturn(true);

		$this->assertNull($gatekeeper->basicAuth());
	}

	/**
	 *
	 */

	public function testBasicAuthLoggingIn()
	{
		$request = $this->getRequest();

		$request->shouldReceive('username')->once()->andReturn('foo@example.org');

		$request->shouldReceive('password')->once()->andReturn('password');

		$gatekeeper = m::mock('\mako\auth\Gatekeeper', [$request, $this->getResponse(), $this->getSession(), $this->getUserProvider(), $this->getGroupProvider()])->makePartial();

		$gatekeeper->shouldReceive('isLoggedIn')->once()->andReturn(false);

		$gatekeeper->shouldReceive('login')->once()->with('foo@example.org', 'password')->andReturn(true);

		$this->assertNull($gatekeeper->basicAuth());
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

		$response = $this->getResponse();

		$response->shouldReceive('deleteCookie')->once()->with('gatekeeper_auth_key', $this->getCookieOptions());

		$gatekeeper = new Gatekeeper($this->getRequest(), $response, $session, $this->getUserProvider(), $this->getGroupProvider());

		$gatekeeper->logout();
	}
}