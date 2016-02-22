<?php

namespace mako\tests\integration\database\midgard\relations;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class ManyToManyUser extends \TestORM
{
	protected $tableName = 'users';

	public function groups()
	{
		return $this->manyToMany('mako\tests\integration\database\midgard\relations\ManyToManyGroup', 'user_id', 'groups_users', 'group_id');
	}
}

class ManyToManyGroup extends \TestORM
{
	protected $tableName = 'groups';

	public function users()
	{
		return $this->manyToMany('mako\tests\integration\database\midgard\relations\ManyToManyUser', 'group_id', 'groups_users', 'user_id');
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group integration
 * @group integration:database
 * @requires extension PDO
 * @requires extension pdo_sqlite
 */
class ManyToManyTest extends \ORMTestCase
{
	/**
	 *
	 */
	public function testBasicManyToManyRelation1()
	{
		$user = ManyToManyUser::get(1);

		$groups = $user->groups;

		$this->assertInstanceOf('mako\database\midgard\ResultSet', $groups);

		$this->assertEquals(2, count($groups));

		foreach($groups as $group)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\ManyToManyGroup', $group);
		}

		$this->assertEquals('admin', $groups[0]->name);

		$this->assertEquals('user', $groups[1]->name);
	}

	/**
	 *
	 */
	public function testBasicManyToManyRelation2()
	{
		$group = ManyToManyGroup::get(1);

		$users = $group->users;

		$this->assertInstanceOf('mako\database\midgard\ResultSet', $users);

		$this->assertEquals(1, count($users));

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\ManyToManyUser', $user);
		}

		$this->assertEquals('foo', $users[0]->username);
	}

	/**
	 *
	 */
	public function testLazyHasManyRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = ManyToManyUser::ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->groups);

			foreach($user->groups as $group)
			{
				$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\ManyToManyGroup', $group);
			}
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(4, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */
	public function testEagerHasManyRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = ManyToManyUser::including('groups')->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->groups);

			foreach($user->groups as $group)
			{
				$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\ManyToManyGroup', $group);
			}
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */
	public function testEagerHasManyRelationWithConstraint()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = ManyToManyUser::including(['groups' => function($query)
		{
			$query->where('name', '=', 'does not exist');
		}])->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->groups);

			$this->assertEquals(0, count($user->groups));
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */
	public function testLinkAndUnlinkUsingId()
	{
		$user = ManyToManyUser::get(3);

		$group = ManyToManyGroup::get(1);

		$this->assertEquals(1, count($user->groups()->all()));

		$this->assertEquals(1, count($group->users()->all()));

		$user->groups()->link($group->id);

		$this->assertEquals(2, count($user->groups()->all()));

		$this->assertEquals(2, count($group->users()->all()));

		$user->groups()->unlink($group->id);

		$this->assertEquals(1, count($user->groups()->all()));

		$this->assertEquals(1, count($group->users()->all()));

		$group->users()->link($user->id);

		$this->assertEquals(2, count($user->groups()->all()));

		$this->assertEquals(2, count($group->users()->all()));

		$group->users()->unlink($user->id);

		$this->assertEquals(1, count($user->groups()->all()));

		$this->assertEquals(1, count($group->users()->all()));
	}

	/**
	 *
	 */
	public function testLinkAndUnlinkUsingModel()
	{
		$user = ManyToManyUser::get(3);

		$group = ManyToManyGroup::get(1);

		$this->assertEquals(1, count($user->groups()->all()));

		$this->assertEquals(1, count($group->users()->all()));

		$user->groups()->link($group);

		$this->assertEquals(2, count($user->groups()->all()));

		$this->assertEquals(2, count($group->users()->all()));

		$user->groups()->unlink($group);

		$this->assertEquals(1, count($user->groups()->all()));

		$this->assertEquals(1, count($group->users()->all()));

		$group->users()->link($user);

		$this->assertEquals(2, count($user->groups()->all()));

		$this->assertEquals(2, count($group->users()->all()));

		$group->users()->unlink($user);

		$this->assertEquals(1, count($user->groups()->all()));

		$this->assertEquals(1, count($group->users()->all()));
	}

	/**
	 *
	 */
	public function testLinkAndUnlinkUsingArrayOfIds()
	{
		$user = ManyToManyUser::get(3);

		$group1 = ManyToManyGroup::get(1);
		$group2 = ManyToManyGroup::get(4);

		$this->assertEquals(1, count($user->groups()->all()));

		$user->groups()->link([$group1->id, $group2->id]);

		$this->assertEquals(3, count($user->groups()->all()));

		$user->groups()->unlink([$group1->id, $group2->id]);

		$this->assertEquals(1, count($user->groups()->all()));
	}

	/**
	 *
	 */
	public function testLinkAndUnlinkUsingArrayOfModels()
	{
		$user = ManyToManyUser::get(3);

		$group1 = ManyToManyGroup::get(1);
		$group2 = ManyToManyGroup::get(4);

		$this->assertEquals(1, count($user->groups()->all()));

		$user->groups()->link([$group1, $group2]);

		$this->assertEquals(3, count($user->groups()->all()));

		$user->groups()->unlink([$group1, $group2]);

		$this->assertEquals(1, count($user->groups()->all()));
	}

	/**
	 *
	 */
	public function testSynchronizeUsingArrayOfIds()
	{
		$user = ManyToManyUser::get(3);

		$group1 = ManyToManyGroup::get(1);
		$group2 = ManyToManyGroup::get(2);
		$group3 = ManyToManyGroup::get(4);

		$this->assertEquals(1, count($user->groups()->all()));

		$user->groups()->synchronize([$group1->id, $group2->id, $group3->id]);

		$this->assertEquals(3, count($user->groups()->all()));

		$user->groups()->synchronize([$group2->id]);

		$this->assertEquals(1, count($user->groups()->all()));
	}
}