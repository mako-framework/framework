<?php

namespace mako\tests\unit\auth;

use \mako\auth\Gatekeeper;

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

	public function getSession()
	{
		return m::mock('\mako\session\Session');
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
			'httponly' => false,
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
}