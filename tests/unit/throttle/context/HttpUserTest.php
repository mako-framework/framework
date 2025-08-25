<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\throttle\context;

use mako\gatekeeper\entities\user\UserEntityInterface;
use mako\gatekeeper\Gatekeeper;
use mako\http\Request;
use mako\tests\TestCase;
use mako\throttle\context\HttpUser;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class HttpUserTest extends TestCase
{
	/**
	 *
	 */
	public function testWithNoGatekeeper(): void
	{
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getIp')->once()->andReturn('127.0.0.1');

		$httpUser = new HttpUser($request);

		$this->assertSame('127.0.0.1', $httpUser->getIdentifier());
	}

	/**
	 *
	 */
	public function testWithGatekeeperWithNoUser(): void
	{
		$request = Mockery::mock(Request::class);

		$gatekeeper = Mockery::mock(Gatekeeper::class);

		$gatekeeper->shouldReceive('isLoggedIn')->once()->andReturn(false);

		$request->shouldReceive('getIp')->once()->andReturn('127.0.0.1');

		$httpUser = new HttpUser($request, $gatekeeper);

		$this->assertSame('127.0.0.1', $httpUser->getIdentifier());
	}

	/**
	 *
	 */
	public function testWithGatekeeperWithUser(): void
	{
		$request = Mockery::mock(Request::class);

		$gatekeeper = Mockery::mock(Gatekeeper::class);

		$user = Mockery::mock(UserEntityInterface::class);

		$user->shouldReceive('getId')->once()->andReturn(42);

		$gatekeeper->shouldReceive('isLoggedIn')->once()->andReturn(true);

		$gatekeeper->shouldReceive('getUser')->once()->andReturn($user);

		$httpUser = new HttpUser($request, $gatekeeper);

		$this->assertSame('42', $httpUser->getIdentifier());
	}
}
