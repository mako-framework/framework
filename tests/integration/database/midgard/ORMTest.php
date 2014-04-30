<?php

namespace mako\tests\integration\database\midgard;

use \mako\database\ConnectionManager;

use \DateTime;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class TestORM extends \mako\database\midgard\ORM
{
	
}

class TestUser extends TestORM
{
	protected $tableName = 'users';
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group integration
 * @requires extension PDO
 * @requires extension pdo_sqlite
 */

class ORMTest extends \PHPUnit_Framework_TestCase
{
	protected $connectionManager;

	/**
	 * 
	 */

	public function setup()
	{
		// Set up connection manager

		$configs = 
		[
			'sqlite' => 
			[
				'dsn'         => 'sqlite::memory:',
				'log_queries' => false,
				'queries'     => 
				[
					"PRAGMA encoding = 'UTF-8'",
				],
			],
		];

		$this->connectionManager = new ConnectionManager('sqlite', $configs);

		// Load test database into memory

		$sql = file_get_contents(MAKO_TESTS_PATH . '/integration/resources/sqlite.sql');

		$this->connectionManager->connection()->getPDO()->exec($sql);

		// Set the connection manager

		TestOrm::setConnectionManager($this->connectionManager);
	}

	/**
	 * 
	 */

	public function testGet()
	{
		$user = TestUser::get(1);

		$this->assertInstanceOf('mako\tests\integration\database\midgard\TestUser', $user);

		$this->assertEquals(1, $user->id);

		$this->assertEquals('2014-04-30 14:40:01', $user->created_at);

		$this->assertEquals('foo', $user->username);

		$this->assertEquals('foo@example.org', $user->email);
	}

	/**
	 * 
	 */

	public function testGetNonExistent()
	{
		$user = TestUser::get(999);

		$this->assertFalse($user);
	}

	/**
	 * 
	 */

	public function testReload()
	{
		$user = TestUser::get(1);

		$user->username = 'bax';

		$this->assertEquals('bax', $user->username);

		$reloaded = $user->reload();

		$this->assertTrue($reloaded);

		$this->assertEquals('foo', $user->username);
	}

	/**
	 * 
	 */

	public function testReloadNonExistent()
	{
		$user = new TestUser;

		$reloaded = $user->reload();

		$this->assertFalse($reloaded);
	}

	/**
	 * 
	 */

	public function testCreate()
	{
		$dateTime = new DateTime;

		$user = TestUser::create(['username' => 'bax', 'email' => 'bax@example.org', 'created_at' => $dateTime]);

		$this->assertEquals(4, $user->id);

		$this->assertEquals('bax', $user->username);

		$this->assertEquals('bax@example.org', $user->email);

		$this->assertEquals($dateTime, $user->created_at);

		$user->delete();
	}

	/**
	 * 
	 */

	public function testClone()
	{
		$user = TestUser::get(1);

		$clone = clone $user;

		$this->assertTrue(empty($clone->id));

		$this->assertEquals($clone->created_at, $user->created_at);

		$this->assertEquals($clone->username, $user->username);

		$this->assertEquals($clone->email, $user->email);

		$clone->save();

		$this->assertEquals(4, $clone->id);

		$clone->delete();
	}

	/**
	 * 
	 */

	public function testToArray()
	{
		$user = TestUser::get(1)->toArray();

		$this->assertEquals(['id' => '1', 'created_at' => '2014-04-30 14:40:01', 'username' => 'foo', 'email' => 'foo@example.org'], $user);
	}

	/**
	 * 
	 */

	public function testToJSON()
	{
		$user = TestUser::get(1)->toJSON();

		$this->assertEquals('{"id":"1","created_at":"2014-04-30 14:40:01","username":"foo","email":"foo@example.org"}', $user);
	}

	/**
	 * 
	 */

	public function testHydratorForwarding()
	{
		$user = TestUser::where('id', '=', 1)->first();

		$this->assertInstanceOf('mako\tests\integration\database\midgard\TestUser', $user);
	}
}