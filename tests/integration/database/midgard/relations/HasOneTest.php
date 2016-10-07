<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard\relations;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class HasOneUser extends \TestORM
{
	protected $tableName = 'users';

	public function profile()
	{
		return $this->hasOne('mako\tests\integration\database\midgard\relations\HasOneProfile', 'user_id');
	}
}

class HasOneProfile extends \TestORM
{
	protected $tableName = 'profiles';
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
class HasOneTest extends \ORMTestCase
{
	/**
	 *
	 */
	public function testBasicHasOneRelation()
	{
		$user = HasOneUser::get(1);

		$profile = $user->profile;

		$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOneProfile', $profile);

		$this->assertEquals($user->id, $profile->user_id);

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "user_id" = \'1\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testBelongsToYield()
	{
		$user = HasOneUser::get(1);

		$generator = $user->profile()->yield();

		$this->assertInstanceOf('Generator', $generator);

		$count = 0;

		foreach($generator as $profile)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOneProfile', $profile);

			$this->assertEquals($user->id, $profile->user_id);

			$count++;
		}

		$this->assertEquals(1, $count);

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "user_id" = \'1\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testLazyHasOneRelation()
	{
		$users = HasOneUser::ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOneProfile', $user->profile);

			$this->assertEquals($user->id, $user->profile->user_id);
		}

		$this->assertEquals(4, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "user_id" = \'1\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "user_id" = \'2\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[2]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "user_id" = \'3\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[3]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasOneRelation()
	{
		$users = HasOneUser::including('profile')->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOneProfile', $user->profile);

			$this->assertEquals($user->id, $user->profile->user_id);
		}

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "user_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasOneRelationWithConstraint()
	{
		$users = HasOneUser::including(['profile' => function($query)
		{
			$query->where('interests', '=', 'does not exist');
		}])->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertFalse($user->profile);
		}

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "interests" = \'does not exist\' AND "user_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testCreateRelated()
	{
		$user = new HasOneUser();

		$user->created_at = '2014-04-30 14:12:43';

		$user->username = 'bax';

		$user->email = 'bax@example.org';

		$user->save();

		$profile = new HasOneProfile();

		$profile->interests = 'gaming';

		$user->profile()->create($profile);

		$this->assertEquals($user->id, $profile->user_id);

		$profile->delete();

		$user->delete();

		$this->assertEquals(4, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('INSERT INTO "users" ("created_at", "username", "email") VALUES (\'2014-04-30 14:12:43\', \'bax\', \'bax@example.org\')', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('INSERT INTO "profiles" ("interests", "user_id") VALUES (\'gaming\', \'4\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}
}