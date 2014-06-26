<?php

namespace mako\tests\unit\auth\providers;

use \mako\auth\providers\UserProvider;

use \Mockery as m;

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

	public function testGetById()
	{
		$user = $this->getUser();

		$user->shouldReceive('where')->once()->with('id', '=', 1)->andReturn($user);

		$user->shouldReceive('first')->once()->andReturn($user);

		$userProvider = new UserProvider($user);

		$this->assertInstanceOf('mako\auth\user\User', $userProvider->getById(1));
	}
}