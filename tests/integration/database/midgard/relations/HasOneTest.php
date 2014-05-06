<?php

namespace mako\tests\integration\database\relations\midgard;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class HasOneUser extends \TestORM
{
	protected $tableName = 'users';

	public function profile()
	{
		return $this->hasOne('mako\tests\integration\database\relations\midgard\HasOneProfile', 'user_id');
	}
}

class HasOneProfile extends \TestORM
{
	protected $tableName = 'profiles';

	public function user()
	{
		return $this->belongsTo('mako\tests\integration\database\relations\midgard\HasOneUser', 'user_id');
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

class HasOneTest extends \ORMTestCase
{
	/**
	 * 
	 */

	public function testBasicHasOneRelation()
	{
		$user = HasOneUser::get(1);

		$profile = $user->profile;

		$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\HasOneProfile', $profile);

		$this->assertEquals('music', $profile->interests);
	}

	/**
	 * 
	 */

	public function testBasicBelongsToRelation()
	{
		$profile = HasOneProfile::get(1);

		$user = $profile->user;

		$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\HasOneUser', $user);

		$this->assertEquals('foo', $user->username);
	}

	/**
	 * 
	 */

	public function testLoopHasOneRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = HasOneUser::ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\HasOneProfile', $user->profile);
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(4, $queryCountAfter - $queryCountBefore);
	}

	/**
	 * 
	 */

	public function testEagerLoopHasOneRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = HasOneUser::including('profile')->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\HasOneProfile', $user->profile);
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}
}