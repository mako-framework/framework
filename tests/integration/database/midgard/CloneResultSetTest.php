<?php

namespace mako\tests\integration\database\midgard;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class CloneUser extends \TestORM
{
	protected $tableName = 'users';
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

class CloneResultSetTest extends \ORMTestCase
{
	/**
	 * 
	 */

	public function testClone()
	{
		$count = CloneUser::count();

		$clones = clone CloneUser::ascending('id')->all();

		foreach($clones as $clone)
		{
			$clone->save();
		}

		$this->assertEquals(($count * 2), CloneUser::count());

		$users = CloneUser::ascending('id')->all();

		$chunkedUsers = $users->chunk(3);

		foreach($chunkedUsers[0] as $key => $user)
		{
			$this->assertNotEquals($user->id, $chunkedUsers[1][$key]->id);
			$this->assertEquals($user->created_at, $chunkedUsers[1][$key]->created_at);
			$this->assertEquals($user->username, $chunkedUsers[1][$key]->username);
			$this->assertEquals($user->email, $chunkedUsers[1][$key]->email);
		}
	}
}