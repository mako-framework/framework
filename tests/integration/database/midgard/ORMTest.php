<?php

use \mako\database\ConnectionManager;

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

class ORMTest extends PHPUnit_Framework_TestCase
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

		$this->connectionManager->connection()->getPDO()->exec(file_get_contents(realpath(__DIR__ . '/../../resources/sqlite.sql')));

		// Set the connection manager

		TestOrm::setConnectionManager($this->connectionManager);
	}

	/**
	 * 
	 */

	public function testGet()
	{
		$user = TestUser::get(1);

		$this->assertInstanceOf('TestUser', $user);

		$this->assertEquals(1, $user->id);

		$this->assertEquals('2014-04-30 14:40:01', $user->created_at);

		$this->assertEquals('foo', $user->username);

		$this->assertEquals('foo@example.org', $user->email);
	}

	public function testGetNonExistant()
	{
		$user = TestUser::get(999);

		$this->assertFalse($user);
	}
}