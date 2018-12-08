<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard\relations;

use mako\tests\integration\ORMTestCase;
use mako\tests\integration\TestORM;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class ManyToManyUser extends TestORM
{
	protected $tableName = 'users';

	public function groups()
	{
		return $this->manyToMany('mako\tests\integration\database\midgard\relations\ManyToManyGroup', 'user_id', 'groups_users', 'group_id');
	}
}

class ManyToManyGroup extends TestORM
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
class ManyToManyTest extends ORMTestCase
{
	/**
	 *
	 */
	public function testBasicManyToManyRelation1(): void
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

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT "groups".* FROM "groups" INNER JOIN "groups_users" ON "groups_users"."group_id" = "groups"."id" WHERE "groups_users"."user_id" = \'1\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testManyToManyYield(): void
	{
		$user = ManyToManyUser::get(1);

		$generator = $user->groups()->yield();

		$this->assertInstanceOf('Generator', $generator);

		$count = 0;

		foreach($generator as $group)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\ManyToManyGroup', $group);

			$count++;
		}

		$this->assertEquals(2, $count);

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT "groups".* FROM "groups" INNER JOIN "groups_users" ON "groups_users"."group_id" = "groups"."id" WHERE "groups_users"."user_id" = \'1\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testBasicManyToManyRelation2(): void
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

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "groups" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT "users".* FROM "users" INNER JOIN "groups_users" ON "groups_users"."user_id" = "users"."id" WHERE "groups_users"."group_id" = \'1\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testManyToManyWithExtraColumns(): void
	{
		$user = ManyToManyUser::get(1);

		$groups = $user->groups()->alongWith(['extra'])->all();

		$this->assertInstanceOf('mako\database\midgard\ResultSet', $groups);

		$this->assertEquals(2, count($groups));

		foreach($groups as $group)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\ManyToManyGroup', $group);
		}

		$this->assertEquals('foobar', $groups[0]->extra);

		$this->assertNull($groups[1]->extra);

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT "groups".*, "groups_users"."extra" FROM "groups" INNER JOIN "groups_users" ON "groups_users"."group_id" = "groups"."id" WHERE "groups_users"."user_id" = \'1\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testManyToManyWithExtraColumnsWithAlias(): void
	{
		$user = ManyToManyUser::get(1);

		$groups = $user->groups()->alongWith(['extra as additional'])->all();

		$this->assertInstanceOf('mako\database\midgard\ResultSet', $groups);

		$this->assertEquals(2, count($groups));

		foreach($groups as $group)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\ManyToManyGroup', $group);
		}

		$this->assertEquals('foobar', $groups[0]->additional);

		$this->assertNull($groups[1]->additional);

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT "groups".*, "groups_users"."extra" AS "additional" FROM "groups" INNER JOIN "groups_users" ON "groups_users"."group_id" = "groups"."id" WHERE "groups_users"."user_id" = \'1\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testManyToManyAggregateWithExtraColumns(): void
	{
		$user = ManyToManyUser::get(1);

		$groups = $user->groups()->alongWith(['extra'])->count();

		$this->assertEquals(2, $groups);

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT COUNT(*) FROM "groups" INNER JOIN "groups_users" ON "groups_users"."group_id" = "groups"."id" WHERE "groups_users"."user_id" = \'1\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testLazyHasManyRelation(): void
	{
		$users = ManyToManyUser::ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->groups);

			foreach($user->groups as $group)
			{
				$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\ManyToManyGroup', $group);
			}
		}

		$this->assertEquals(4, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT "groups".* FROM "groups" INNER JOIN "groups_users" ON "groups_users"."group_id" = "groups"."id" WHERE "groups_users"."user_id" = \'1\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);

		$this->assertEquals('SELECT "groups".* FROM "groups" INNER JOIN "groups_users" ON "groups_users"."group_id" = "groups"."id" WHERE "groups_users"."user_id" = \'2\'', $this->connectionManager->connection('sqlite')->getLog()[2]['query']);

		$this->assertEquals('SELECT "groups".* FROM "groups" INNER JOIN "groups_users" ON "groups_users"."group_id" = "groups"."id" WHERE "groups_users"."user_id" = \'3\'', $this->connectionManager->connection('sqlite')->getLog()[3]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasManyRelation(): void
	{
		$users = ManyToManyUser::including('groups')->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->groups);

			foreach($user->groups as $group)
			{
				$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\ManyToManyGroup', $group);
			}
		}

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT "groups".*, "groups_users"."user_id" FROM "groups" INNER JOIN "groups_users" ON "groups_users"."group_id" = "groups"."id" WHERE "groups_users"."user_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasManyRelationWithConstraint(): void
	{
		$users = ManyToManyUser::including(['groups' => function($query): void
		{
			$query->where('name', '=', 'does not exist');
		}, ])->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->groups);

			$this->assertEquals(0, count($user->groups));
		}

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT "groups".*, "groups_users"."user_id" FROM "groups" INNER JOIN "groups_users" ON "groups_users"."group_id" = "groups"."id" WHERE "name" = \'does not exist\' AND "groups_users"."user_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testLinkAndUnlinkUsingId(): void
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
	public function testLinkAndUnlinkUsingModel(): void
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
	public function testLinkAndUnlinkUsingArrayOfIds(): void
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
	public function testLinkAndUnlinkUsingArrayOfModels(): void
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
	public function testSynchronizeUsingArrayOfIds(): void
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

	/**
	 *
	 */
	public function testLinkWithSameAttributes(): void
	{
		$user = ManyToManyUser::get(3);

		$group1 = ManyToManyGroup::get(1);
		$group2 = ManyToManyGroup::get(4);

		$this->assertEquals(1, count($user->groups()->all()));

		$user->groups()->link([$group1->id, $group2->id], ['extra' => 'barfoo']);

		$groups = $user->groups()->alongWith(['extra'])->all();

		$this->assertEquals(3, count($groups));

		$this->assertSame('barfoo', $groups[1]->extra);
		$this->assertSame('barfoo', $groups[2]->extra);

		$user->groups()->unlink([$group1->id, $group2->id]);

		$this->assertEquals(1, count($user->groups()->all()));
	}

	/**
	 *
	 */
	public function testLinkWithDifferentAttributes(): void
	{
		$user = ManyToManyUser::get(3);

		$group1 = ManyToManyGroup::get(1);
		$group2 = ManyToManyGroup::get(4);

		$this->assertEquals(1, count($user->groups()->all()));

		$user->groups()->link([$group1->id, $group2->id], [['extra' => 'barfoo'], ['extra' => 'bazbax']]);

		$groups = $user->groups()->alongWith(['extra'])->all();

		$this->assertEquals(3, count($groups));

		$this->assertSame('barfoo', $groups[1]->extra);
		$this->assertSame('bazbax', $groups[2]->extra);

		$user->groups()->unlink([$group1->id, $group2->id]);

		$this->assertEquals(1, count($user->groups()->all()));
	}

	/**
	 *
	 */
	public function testUpdateLink(): void
	{
		$user = ManyToManyUser::get(1);

		$groups = $user->groups()->alongWith(['extra'])->all();

		$this->assertEquals(2, count($groups));

		$this->assertSame('foobar', $groups[0]->extra);
		$this->assertSame(null, $groups[1]->extra);

		$user->groups()->updateLink([$groups[0], $groups[1]], ['extra' => 'barfoo']);

		$groups = $user->groups()->alongWith(['extra'])->all();

		$this->assertSame('barfoo', $groups[0]->extra);
		$this->assertSame('barfoo', $groups[1]->extra);

		$user->groups()->updateLink([$groups[0], $groups[1]], [['extra' => 'foobar'], ['extra' => null]]);

		$groups = $user->groups()->alongWith(['extra'])->all();

		$this->assertSame('foobar', $groups[0]->extra);
		$this->assertSame(null, $groups[1]->extra);
	}
}
