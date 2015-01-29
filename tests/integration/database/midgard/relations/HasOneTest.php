<?php

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
	}

	/**
	 *
	 */

	public function testLazyHasOneRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = HasOneUser::ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOneProfile', $user->profile);

			$this->assertEquals($user->id, $user->profile->user_id);
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(4, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testEagerHasOneRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = HasOneUser::including('profile')->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOneProfile', $user->profile);

			$this->assertEquals($user->id, $user->profile->user_id);
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testEagerHasOneRelationWithConstraint()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = HasOneUser::including(['profile' => function($query)
		{
			$query->where('interests', '=', 'does not exist');
		}])->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertFalse($user->profile);
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
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
	}
}