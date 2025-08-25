<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\entities\user;

use DateTime;
use mako\chrono\Time;
use mako\gatekeeper\entities\group\Group;
use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\exceptions\GatekeeperException;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group as GroupAttribute;

#[GroupAttribute('unit')]
class UserTest extends TestCase
{
	/**
	 *
	 */
	public function testGetId(): void
	{
		$user = new User(['id' => 1]);

		$this->assertSame(1, $user->getId());
	}

	/**
	 *
	 */
	public function testSetAndGetEmail(): void
	{
		$user = new User(['email' => 'foo@example.org']);

		$this->assertSame('foo@example.org', $user->getEmail());

		$user->setEmail('bar@example.org');

		$this->assertSame('bar@example.org', $user->getEmail());
	}

	/**
	 *
	 */
	public function testSetAndGetUsername(): void
	{
		$user = new User(['username' => 'foo']);

		$this->assertSame('foo', $user->getUsername());

		$user->setUsername('bar');

		$this->assertSame('bar', $user->getUsername());
	}

	/**
	 *
	 */
	public function testSetAndGetPassword(): void
	{
		$user = new User(['password' => 'hash'], true);

		$this->assertSame('hash', $user->getPassword());

		$user->setPassword('password');

		$this->assertNotSame('hash', $user->getPassword());

		$this->assertNotSame('password', $user->getPassword());
	}

	/**
	 *
	 */
	public function testSetAndGetIp(): void
	{
		$user = new User(['ip' => '::1']);

		$this->assertSame('::1', $user->getIp());

		$user->setIp('127.0.0.1');

		$this->assertSame('127.0.0.1', $user->getIp());
	}

	/**
	 *
	 */
	public function testGenerateActionTokenAndGetActionToken(): void
	{
		$user = new User(['action_token' => 'token']);

		$this->assertSame('token', $user->getActionToken());

		$token = $user->generateActionToken();

		$this->assertSame($token, $user->getActionToken());
	}

	/**
	 *
	 */
	public function testGenerateAccessTokenAndGetAccessToken(): void
	{
		$user = new User(['access_token' => 'token']);

		$this->assertSame('token', $user->getAccessToken());

		$token = $user->generateAccessToken();

		$this->assertSame($token, $user->getAccessToken());
	}

	/**
	 *
	 */
	public function testActiveDeactivateAndIsActivated(): void
	{
		$user = new User(['activated' => 0]);

		$this->assertFalse($user->isActivated());

		$user->activate();

		$this->assertTrue($user->isActivated());

		$user->deactivate();

		$this->assertFalse($user->isActivated());
	}

	/**
	 *
	 */
	public function testBanUnbanAndIsBanne(): void
	{
		$user = new User(['banned' => 0]);

		$this->assertFalse($user->isBanned());

		$user->ban();

		$this->assertTrue($user->isBanned());

		$user->unban();

		$this->assertFalse($user->isBanned());
	}

	/**
	 *
	 */
	public function testValidatePassword(): void
	{
		$user = new User(['password' => 'test']);

		$this->assertTrue($user->validatePassword('test', false));

		$this->assertFalse($user->validatePassword('nope', false));
	}

	/**
	 *
	 */
	public function testIsMemberOfForNonExistingUser(): void
	{
		$this->expectException(GatekeeperException::class);

		$this->expectExceptionMessage('You can only check memberships for users that exist in the database.');

		$user = new User;

		$user->isMemberOf('group');
	}

	/**
	 *
	 */
	public function testIsMemberof(): void
	{
		$user = new User([], false, true, true);

		$group1 = Mockery::mock(Group::class);

		$group1->shouldReceive('getId')->andReturn(1);
		$group1->shouldReceive('getName')->andReturn('foo');

		$group2 = Mockery::mock(Group::class);

		$group2->shouldReceive('getId')->andReturn(2);
		$group2->shouldReceive('getName')->andReturn('bar');

		$user->groups = [$group1, $group2];

		$this->assertFalse($user->isMemberOf(3));

		$this->assertFalse($user->isMemberOf('baz'));

		$this->assertTrue($user->isMemberOf(1));

		$this->assertTrue($user->isMemberOf('foo'));

		$this->assertTrue($user->isMemberOf([3, 1]));

		$this->assertTrue($user->isMemberOf(['baz', 'bar']));

	}

	/**
	 *
	 */
	public function testLockUntilLockedUntilUnlockandIsLocked(): void
	{
		$user = new User(['locked_until' => null]);

		//

		$this->assertNull($user->lockedUntil());

		$this->assertFalse($user->isLocked());

		//

		$user->lockUntil(new DateTime);

		$this->assertInstanceOf(DateTime::class, $user->lockedUntil());

		$this->assertTrue($user->isLocked());

		//

		$user->unlock();

		$this->assertNull($user->lockedUntil());

		$this->assertFalse($user->isLocked());
	}

	/**
	 *
	 */
	public function testGetFailedAttempts(): void
	{
		$user = new User(['failed_attempts' => 3]);

		$this->assertSame(3, $user->getFailedAttempts());
	}

	/**
	 *
	 */
	public function getLastFailAt(): void
	{
		$user = new User(['last_fail_at' => '2017-02-02 12:01:02']);

		$this->assertInstanceOf(Time::class, $user->getLastFailAt());

		$user = new User(['last_fail_at' => null]);

		$this->assertNull($user->getLastFailAt());
	}

	/**
	 *
	 */
	public function testThrottle(): void
	{
		$user = new User(['last_fail_at' => null, 'failed_attempts' => 0, 'locked_until' => null]);

		$user->throttle(2, 3600, false);

		$this->assertSame(1, $user->getFailedAttempts());

		$this->assertFalse($user->isLocked());

		$this->assertInstanceOf(Time::class, $user->getLastFailAt());

		$this->assertNull($user->lockedUntil());
	}

	/**
	 *
	 */
	public function testThrottleWithLimitReached(): void
	{
		$user = new User(['last_fail_at' => Time::now()->rewind(3500), 'failed_attempts' => 1, 'locked_until' => null]);

		$user->throttle(2, 3600, false);

		$this->assertSame(2, $user->getFailedAttempts());

		$this->assertTrue($user->isLocked());

		$this->assertInstanceOf(Time::class, $user->getLastFailAt());

		$this->assertInstanceOf(Time::class, $user->lockedUntil());
	}

	/**
	 *
	 */
	public function testResetThrottle(): void
	{
		$user = new User(['last_fail_at' => Time::now(), 'failed_attempts' => 1, 'locked_until' => Time::now()]);

		$this->assertInstanceOf(Time::class, $user->getLastFailAt());

		$this->assertInstanceOf(Time::class, $user->lockedUntil());

		$this->assertSame(1, $user->getFailedAttempts());

		$user->resetThrottle(false);

		$this->assertNull($user->getLastFailAt());

		$this->assertNull($user->lockedUntil());

		$this->assertSame(0, $user->getFailedAttempts());

	}
}
