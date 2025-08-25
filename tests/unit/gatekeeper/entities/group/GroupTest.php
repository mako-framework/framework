<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\entities\group;

use mako\gatekeeper\entities\group\Group;
use mako\gatekeeper\entities\user\User;
use mako\gatekeeper\exceptions\GatekeeperException;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group as GroupAttribute;

#[GroupAttribute('unit')]
class GroupTest extends TestCase
{
	/**
	 *
	 */
	public function testGetId(): void
	{
		$group = new Group(['id' => 1]);

		$this->assertSame(1, $group->getId());
	}

	/**
	 *
	 */
	public function testSetAndGetName(): void
	{
		$group = new Group(['name' => 'foobar']);

		$this->assertSame('foobar', $group->getName());

		$group->setName('barfoo');

		$this->assertSame('barfoo', $group->getName());
	}

	/**
	 *
	 */
	public function testAddUserToNonExistingGroup(): void
	{
		$this->expectException(GatekeeperException::class);

		$this->expectExceptionMessage('You can only add a user to a group that exist in the database.');

		$user = Mockery::mock(User::class);

		$group = new Group;

		$group->addUser($user);
	}

	/**
	 *
	 */
	public function testAddNonExistingUserToGroup(): void
	{
		$this->expectException(GatekeeperException::class);

		$this->expectExceptionMessage('You can only add a user that exist in the database to a group.');

		$user = Mockery::mock(User::class);

		$user->shouldReceive('isPersisted')->once()->andReturn(false);

		$group = new Group([], false, true, true);

		$group->addUser($user);
	}

	/**
	 *
	 */
	public function testRemoveUserFromNonExistingGroup(): void
	{
		$this->expectException(GatekeeperException::class);

		$this->expectExceptionMessage('You can only remove a user from a group that exist in the database.');

		$user = Mockery::mock(User::class);

		$group = new Group;

		$group->removeUser($user);
	}

 	/**
 	 *
 	 */
 	public function testRemoveNonExistingUserFromGroup(): void
 	{
		$this->expectException(GatekeeperException::class);

		$this->expectExceptionMessage('You can only remove a user that exist in the database from a group.');

		$user = Mockery::mock(User::class);

		$user->shouldReceive('isPersisted')->once()->andReturn(false);

		$group = new Group([], false, true, true);

		$group->removeUser($user);
	}

	/**
	 *
	 */
	public function testIsMemberWithNonExistingGroup(): void
	{
		$this->expectException(GatekeeperException::class);

		$this->expectExceptionMessage('You can only check if a user is a member of a group that exist in the database.');

		$user = Mockery::mock(User::class);

		$group = new Group;

		$group->isMember($user);
	}

 	/**
 	 *
 	 */
 	public function testIsMemberWithNonExistingUser(): void
 	{
		$this->expectException(GatekeeperException::class);

		$this->expectExceptionMessage('You can only check if a user that exist in the database is a member of a group.');

		$user = Mockery::mock(User::class);

		$user->shouldReceive('isPersisted')->once()->andReturn(false);

		$group = new Group([], false, true, true);

		$group->isMember($user);
	}
}
