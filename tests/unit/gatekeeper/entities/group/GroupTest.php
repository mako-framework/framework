<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\entities\group;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\gatekeeper\entities\group\Group;
use mako\gatekeeper\entities\user\User;

/**
 * @group unit
 */
class GroupTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 *
	 */
	public function testGetId()
	{
		$group = new Group(['id' => 1]);

		$this->assertSame(1, $group->getId());
	}

	/**
	 *
	 */
	public function testSetAndGetName()
	{
		$group = new Group(['name' => 'foobar']);

		$this->assertSame('foobar', $group->getName());

		$group->setName('barfoo');

		$this->assertSame('barfoo', $group->getName());
	}

	/**
	 * @expectedException \LogicException
	 * @expectedExceptionMessage You can only add a user to a group that exist in the database.
	 */
	public function testAddUserToNonExistingGroup()
	{
		$user = Mockery::mock(User::class);

		$group = new Group();

		$group->addUser($user);
	}

	/**
	 * @expectedException \LogicException
	 * @expectedExceptionMessage You can only add a user that exist in the database to a group.
	 */
	public function testAddNonExistingUserToGroup()
	{
		$user = Mockery::mock(User::class);

		$user->shouldReceive('exists')->once()->andReturn(false);

		$group = new Group([], false, true, true);

		$group->addUser($user);
	}

	/**
	 * @expectedException \LogicException
	 * @expectedExceptionMessage You can only remove a user from a group that exist in the database.
	 */
	public function testRemoveUserFromNonExistingGroup()
	{
		$user = Mockery::mock(User::class);

		$group = new Group();

		$group->removeUser($user);
	}

	 /**
 	 * @expectedException \LogicException
 	 * @expectedExceptionMessage You can only remove a user that exist in the database from a group.
 	 */
 	public function testRemoveNonExistingUserFromGroup()
	{
		$user = Mockery::mock(User::class);

		$user->shouldReceive('exists')->once()->andReturn(false);

		$group = new Group([], false, true, true);

		$group->removeUser($user);
	}

	/**
	 * @expectedException \LogicException
	 * @expectedExceptionMessage You can only check if a user is a member of a group that exist in the database.
	 */
	public function testIsMemberWithNonExistingGroup()
	{
		$user = Mockery::mock(User::class);

		$group = new Group();

		$group->isMember($user);
	}

	 /**
 	 * @expectedException \LogicException
 	 * @expectedExceptionMessage You can only check if a user that exist in the database is a member of a group.
 	 */
 	public function testIsMemberWithNonExistingUser()
	{
		$user = Mockery::mock(User::class);

		$user->shouldReceive('exists')->once()->andReturn(false);

		$group = new Group([], false, true, true);

		$group->isMember($user);
	}
}
