<?php

namespace mako\tests\integration\database\relations\midgard;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class ManyToManyUser extends \TestORM
{
	protected $tableName = 'users';

	public function groups()
	{
		return $this->manyToMany('mako\tests\integration\database\relations\midgard\ManyToManyGroup', 'user_id', 'groups_users', 'group_id');
	}
}

class ManyToManyGroup extends \TestORM
{
	protected $tableName = 'groups';

	public function users()
	{
		return $this->manyToMany('mako\tests\integration\database\relations\midgard\ManyToManyUser', 'group_id', 'groups_users', 'user_id');
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
			$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\ManyToManyGroup', $group);
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
			$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\ManyToManyUser', $user);
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
				$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\ManyToManyGroup', $group);
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

		$users = ManyToManyUser::including(['groups'])->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->groups);

			foreach($user->groups as $group)
			{
				$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\ManyToManyGroup', $group);
			}
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}
}