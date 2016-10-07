<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

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

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "profiles" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = \'1\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testBelongsToYield()
	{
		$profile = BelongsToProfile::get(1);

		$generator = $profile->user()->yield();

		$this->assertInstanceOf('Generator', $generator);

		$count = 0;

		foreach($generator as $user)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\BelongsToUser', $user);

			$this->assertEquals($user->id, $profile->user_id);

			$count++;
		}

		$this->assertEquals(1, $count);

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "profiles" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = \'1\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testLazyBelongsToRelation()
	{
		$profiles = BelongsToProfile::ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\BelongsToUser', $profile->user);

			$this->assertEquals($profile->user_id, $profile->user->id);
		}

		$this->assertEquals(4, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "profiles" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = \'1\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = \'2\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[2]['query']);

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = \'3\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[3]['query']);
	}

	/**
	 *
	 */
	public function testEagerBelongsToRelation()
	{
		$profiles = BelongsToProfile::including('user')->ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\BelongsToUser', $profile->user);

			$this->assertEquals($profile->user_id, $profile->user->id);
		}

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "profiles" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" WHERE "id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testEagerBelongsToRelationWithConstraint()
	{
		$profiles = BelongsToProfile::including(['user' => function($query)
		{
			$query->where('username', '=', 'does not exist');
		}])->ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertFalse($profile->user);
		}

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "profiles" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "users" WHERE "username" = \'does not exist\' AND "id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}
}