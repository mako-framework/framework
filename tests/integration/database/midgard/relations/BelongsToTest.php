<?php

namespace mako\tests\integration\database\relations\midgard;

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
		return $this->belongsTo('mako\tests\integration\database\relations\midgard\BelongsToUser', 'user_id');
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

		$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\BelongsToUser', $user);

		$this->assertEquals('foo', $user->username);
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
			$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\BelongsToUser', $profile->user);
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

		$profiles = BelongsToProfile::including(['user'])->ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\BelongsToUser', $profile->user);
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}
}