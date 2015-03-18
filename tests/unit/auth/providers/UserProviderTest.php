<?php

namespace mako\tests\unit\auth\providers;

use mako\auth\providers\UserProvider;

use DateTime;
use Mockery as m;

/**
 * @group unit
 */

class UserProviderTest extends \PHPUnit_Framework_TestCase
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

	public function getUser()
	{
		return m::mock('overload:mako\auth\user\User')->shouldDeferMissing();
	}

	/**
	 *
	 */

	public function testCreateUser()
	{
		$user = $this->getUser();

		$user->shouldReceive('setEmail')->once()->with('foobar@example.org');

		$user->shouldReceive('setUsername')->once()->with('foobar');

		$user->shouldReceive('setPassword')->once()->with('password');

		$user->shouldReceive('setIp')->once()->with('127.0.0.1');

		$user->shouldReceive('save')->once();

		$userProvider = new UserProvider($user);

		$this->assertInstanceOf('mako\auth\user\User', $userProvider->createUser('foobar@example.org', 'foobar', 'password', '127.0.0.1'));
	}

	/**
	 *
	 */

	public function testGetByActionToken()
	{
		$user = $this->getUser();

		$user->shouldReceive('where')->once()->with('action_token', '=', 'token')->andReturn($user);

		$user->shouldReceive('first')->once()->andReturn($user);

		$userProvider = new UserProvider($user);

		$this->assertInstanceOf('mako\auth\user\User', $userProvider->getByActionToken('token'));
	}

	/**
	 *
	 */

	public function testGetByAccessToken()
	{
		$user = $this->getUser();

		$user->shouldReceive('where')->once()->with('access_token', '=', 'token')->andReturn($user);

		$user->shouldReceive('first')->once()->andReturn($user);

		$userProvider = new UserProvider($user);

		$this->assertInstanceOf('mako\auth\user\User', $userProvider->getByAccessToken('token'));
	}

	/**
	 *
	 */

	public function testGetByEmail()
	{
		$user = $this->getUser();

		$user->shouldReceive('where')->once()->with('email', '=', 'foobar@example.org')->andReturn($user);

		$user->shouldReceive('first')->once()->andReturn($user);

		$userProvider = new UserProvider($user);

		$this->assertInstanceOf('mako\auth\user\User', $userProvider->getByEmail('foobar@example.org'));
	}

	/**
	 *
	 */

	public function testGetByUsername()
	{
		$user = $this->getUser();

		$user->shouldReceive('where')->once()->with('username', '=', 'foobar')->andReturn($user);

		$user->shouldReceive('first')->once()->andReturn($user);

		$userProvider = new UserProvider($user);

		$this->assertInstanceOf('mako\auth\user\User', $userProvider->getByUsername('foobar'));
	}

	/**
	 *
	 */

	public function testGetById()
	{
		$user = $this->getUser();

		$user->shouldReceive('where')->once()->with('id', '=', 1)->andReturn($user);

		$user->shouldReceive('first')->once()->andReturn($user);

		$userProvider = new UserProvider($user);

		$this->assertInstanceOf('mako\auth\user\User', $userProvider->getById(1));
	}

	/**
	 *
	 */

	public function testThrottleWithNoLastFailedAttempts()
	{
		$userProvider = new UserProvider($this->getUser());

		$user = m::mock('mako\auth\user\UserInterface');

		$user->shouldReceive('getLastFailAt')->once()->andReturn(null);

		$user->shouldReceive('incrementFailedAttempts')->once();

		$user->shouldReceive('setLastFailAt')->once();

		$user->shouldReceive('getFailedAttempts')->andReturn(1);

		$user->shouldReceive('save')->once()->andReturn(true);

		$this->assertTrue($userProvider->throttle($user, 5, 300));
	}

	/**
	 *
	 */

	public function testThrottleWithFailedAttemptsReset()
	{
		$userProvider = new UserProvider($this->getUser());

		$user = m::mock('mako\auth\user\UserInterface');

		$user->shouldReceive('getLastFailAt')->once()->andReturn(new DateTime('1999-01-01 12:12:12'));

		$user->shouldReceive('resetFailedAttempts')->once();

		$user->shouldReceive('incrementFailedAttempts')->once();

		$user->shouldReceive('setLastFailAt')->once();

		$user->shouldReceive('getFailedAttempts')->andReturn(1);

		$user->shouldReceive('save')->once()->andReturn(true);

		$this->assertTrue($userProvider->throttle($user, 5, 300));
	}

	/**
	 *
	 */

	public function testThrottleWithLock()
	{
		$userProvider = new UserProvider($this->getUser());

		$user = m::mock('mako\auth\user\UserInterface');

		$user->shouldReceive('getLastFailAt')->once()->andReturn(null);

		$user->shouldReceive('incrementFailedAttempts')->once();

		$user->shouldReceive('setLastFailAt')->once();

		$user->shouldReceive('getFailedAttempts')->andReturn(5);

		$user->shouldReceive('lockUntil')->once();

		$user->shouldReceive('save')->once()->andReturn(true);

		$this->assertTrue($userProvider->throttle($user, 5, 300));
	}

	/**
	 *
	 */

	public function testResetThrottleWithNoFailedAttempts()
	{
		$userProvider = new UserProvider($this->getUser());

		$user = m::mock('mako\auth\user\UserInterface');

		$user->shouldReceive('getFailedAttempts')->once()->andReturn(0);

		$this->assertTrue($userProvider->resetThrottle($user));
	}

	/**
	 *
	 */

	public function testResetThrottleWithFailedAttempts()
	{
		$userProvider = new UserProvider($this->getUser());

		$user = m::mock('mako\auth\user\UserInterface');

		$user->shouldReceive('getFailedAttempts')->once()->andReturn(1);

		$user->shouldReceive('resetFailedAttempts')->once();

		$user->shouldReceive('unlock')->once();

		$user->shouldReceive('save')->once()->andReturn(true);

		$this->assertTrue($userProvider->resetThrottle($user));
	}
}