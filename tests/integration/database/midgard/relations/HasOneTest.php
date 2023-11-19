<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard\relations;

use Generator;
use mako\tests\integration\ORMTestCase;
use mako\tests\integration\TestORM;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class HasOneUser extends TestORM
{
	protected string $tableName = 'users';

	public function profile()
	{
		return $this->hasOne(HasOneProfile::class, 'user_id');
	}
}

class HasOneProfile extends TestORM
{
	protected string $tableName = 'profiles';
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
class HasOneTest extends ORMTestCase
{
	/**
	 *
	 */
	public function testBasicHasOneRelation(): void
	{
		$user = HasOneUser::get(1);

		$profile = $user->profile;

		$this->assertInstanceOf(HasOneProfile::class, $profile);

		$this->assertEquals($user->id, $profile->user_id);

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "profiles"."user_id" = \'1\' LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testBelongsToYield(): void
	{
		$user = HasOneUser::get(1);

		$generator = $user->profile()->yield();

		$this->assertInstanceOf(Generator::class, $generator);

		$count = 0;

		foreach ($generator as $profile) {
			$this->assertInstanceOf(HasOneProfile::class, $profile);

			$this->assertEquals($user->id, $profile->user_id);

			$count++;
		}

		$this->assertEquals(1, $count);

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "profiles"."user_id" = \'1\'', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testLazyHasOneRelation(): void
	{
		$users = (new HasOneUser)->ascending('id')->all();

		foreach ($users as $user) {
			$this->assertInstanceOf(HasOneProfile::class, $user->profile);

			$this->assertEquals($user->id, $user->profile->user_id);
		}

		$this->assertEquals(4, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "profiles"."user_id" = \'1\' LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "profiles"."user_id" = \'2\' LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[2]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "profiles"."user_id" = \'3\' LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[3]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasOneRelation(): void
	{
		$users = (new HasOneUser)->including('profile')->ascending('id')->all();

		foreach ($users as $user) {
			$this->assertInstanceOf(HasOneProfile::class, $user->profile);

			$this->assertEquals($user->id, $user->profile->user_id);
		}

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "profiles"."user_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasOneRelationWithConstraint(): void
	{
		$users = (new HasOneUser)->including(['profile' => function ($query): void {
			$query->where('interests', '=', 'does not exist');
		}, ])->ascending('id')->all();

		foreach ($users as $user) {
			$this->assertNull($user->profile);
		}

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "interests" = \'does not exist\' AND "profiles"."user_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testCreateRelated(): void
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

		$this->assertEquals(4, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('INSERT INTO "users" ("created_at", "username", "email") VALUES (\'2014-04-30 14:12:43\', \'bax\', \'bax@example.org\')', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('INSERT INTO "profiles" ("interests", "user_id") VALUES (\'gaming\', \'4\')', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testWithCountOf(): void
	{
		$user = (new HasOneUser)->withCountOf('profile')->get(1);

		$this->assertEquals(1, $user->profile_count);

		$this->assertEquals('SELECT *, (SELECT COUNT(*) FROM "profiles" WHERE "profiles"."user_id" = "users"."id") AS "profile_count" FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);
	}
}
