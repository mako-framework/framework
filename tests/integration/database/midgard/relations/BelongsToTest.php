<?php

namespace mako\tests\integration\database\midgard\relations;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class BelongsToUser extends \TestORM
{
	protected $tableName = 'users';
}

class BelongsToProfile extends \TestORM
{
	protected $tableName = 'profiles';

	public function user()
	{
		return $this->belongsTo('mako\tests\integration\database\midgard\relations\BelongsToUser', 'user_id');
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

class BelongsToTest extends \ORMTestCase
{
	/**
	 *
	 */

	public function testBasicBelongsToRelation()
	{
		$profile = BelongsToProfile::get(1);

		$user = $profile->user;

		$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\BelongsToUser', $user);

		$this->assertEquals($profile->user_id, $user->id);
	}

	/**
	 *
	 */

	public function testLazyBelongsToRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$profiles = BelongsToProfile::ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\BelongsToUser', $profile->user);

			$this->assertEquals($profile->user_id, $profile->user->id);
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(4, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testEagerBelongsToRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$profiles = BelongsToProfile::including('user')->ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\BelongsToUser', $profile->user);

			$this->assertEquals($profile->user_id, $profile->user->id);
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testEagerBelongsToRelationWithConstraint()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$profiles = BelongsToProfile::including(['user' => function($query)
		{
			$query->where('username', '=', 'does not exist');
		}])->ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertFalse($profile->user);
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}
}