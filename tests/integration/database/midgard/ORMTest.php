<?php

namespace mako\tests\integration\database\midgard;

use \DateTime;

use mako\utility\UUID;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class TestUser extends \TestORM
{
	protected $tableName = 'users';
}

class TestUserReadOnly extends TestUser
{
	protected $readOnly = true;
}

class TestUserScoped extends TestUser
{
	public function withArticlesScope($query)
	{
		return $query->join('articles', 'articles.user_id', '=', 'users.id')->distinct();
	}
}

class TestUserDateTime extends TestUser
{
	protected $cast = ['created_at' => 'date'];
}

class UUIDKey extends \TestORM
{
	protected $tableName = 'uuid_keys';

	protected $primaryKeyType = \TestORM::PRIMARY_KEY_TYPE_UUID;
}

class CustomKey extends \TestORM
{
	protected $tableName = 'custom_keys';

	protected $primaryKeyType = \TestORM::PRIMARY_KEY_TYPE_CUSTOM;

	protected function generatePrimaryKey()
	{
		return 'foobarbax';
	}
}

class NoKey extends \TestORM
{
	protected $tableName = 'no_keys';

	protected $primaryKeyType = \TestORM::PRIMARY_KEY_TYPE_NONE;
}

class Counter extends \TestORM
{
	protected $tableName = 'counters';
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

class ORMTest extends \ORMTestCase
{
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

	public function testAll()
	{
		$users = TestUser::all();

		$this->assertInstanceOf('\mako\database\midgard\ResultSet', $users);

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\TestUser', $user);
		}
	}

	/**
	 *
	 */

	public function testLimitColumnsFirst()
	{
		$user = TestUser::select(['username', 'email'])->where('id', '=', 1)->first();

		$this->assertEquals(['username' => 'foo', 'email' => 'foo@example.org'], $user->getRawColumns());
	}

	/**
	 *
	 */

	public function testLimitColumnsAll()
	{
		$users = TestUser::select(['username', 'email'])->all();

		$this->assertEquals(['username' => 'foo', 'email' => 'foo@example.org'], $users[0]->getRawColumns());
	}

	/**
	 *
	 */

	public function testJoin()
	{
		$users = TestUser::join('articles', 'articles.user_id', '=', 'users.id')->distinct()->all();

		$this->assertEquals(2, count($users));

		$this->assertEquals(['id' => 1, 'created_at' => '2014-04-30 14:40:01', 'username' => 'foo', 'email' => 'foo@example.org'], $users[0]->getRawColumns());

		$this->assertEquals(['id' => 2, 'created_at' => '2014-04-30 14:02:43', 'username' => 'bar', 'email' => 'bar@example.org'], $users[1]->getRawColumns());

		$this->assertTrue($users[0]->isReadOnly());

		$this->assertTrue($users[1]->isReadOnly());
	}

	/**
	 *
	 */

	public function testSave()
	{
		$dateTime = new DateTime;

		$user = new TestUser();

		$user->username = 'bax';

		$user->email = 'bax@example.org';

		$user->created_at = $dateTime;

		$user->save();

		$this->assertFalse(empty($user->id));

		$this->assertEquals('bax', $user->username);

		$this->assertEquals('bax@example.org', $user->email);

		$this->assertEquals($dateTime, $user->created_at);

		$user->delete();
	}

	/**
	 *
	 */

	public function testCreate()
	{
		$dateTime = new DateTime;

		$user = TestUser::create(['username' => 'bax', 'email' => 'bax@example.org', 'created_at' => $dateTime]);

		$this->assertFalse(empty($user->id));

		$this->assertEquals('bax', $user->username);

		$this->assertEquals('bax@example.org', $user->email);

		$this->assertEquals($dateTime, $user->created_at);

		$user->delete();
	}

	/**
	 *
	 */

	public function testUpdate()
	{
		$dateTime = new DateTime;

		$user = TestUser::create(['username' => 'bax', 'email' => 'bax@example.org', 'created_at' => $dateTime]);

		$id = $user->id;

		$user = TestUser::get($id);

		$user->username = 'foo';

		$user->save();

		$user = TestUser::get($id);

		$this->assertEquals('foo', $user->username);

		$user->delete();
	}

	/**
	 *
	 */

	public function testDelete()
	{
		$dateTime = new DateTime;

		$user = TestUser::create(['username' => 'bax', 'email' => 'bax@example.org', 'created_at' => $dateTime]);

		$count = TestUser::count();

		$user->delete();

		$this->assertEquals(($count - 1), TestUser::count());
	}

	/**
	 * @expectedException \mako\database\midgard\ReadOnlyRecordException
	 */

	public function saveReadOnly()
	{
		$dateTime = new DateTime;

		$user = new TestUserReadOnly();

		$user->username = 'bax';

		$user->email = 'bax@example.org';

		$user->created_at = $dateTime;

		$user->save();
	}

	/**
	 * @expectedException \mako\database\midgard\ReadOnlyRecordException
	 */

	public function testCreateReadOnly()
	{
		$dateTime = new DateTime;

		$user = TestUserReadOnly::create(['username' => 'bax', 'email' => 'bax@example.org', 'created_at' => $dateTime]);
	}

	/**
	 * @expectedException \mako\database\midgard\ReadOnlyRecordException
	 */

	public function testUpdateReadOnly()
	{
		$user = TestUserReadOnly::get(1);

		$user->username = 'bax';

		$user->save();
	}

	/**
	 * @expectedException \mako\database\midgard\ReadOnlyRecordException
	 */

	public function testDeleteReadOnly()
	{
		$user = TestUserReadOnly::get(1);

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

		$this->assertFalse(empty($clone->id));

		$clone->delete();
	}

	/**
	 *
	 */

	public function testCloneResultSet()
	{
		$count = TestUser::count();

		$clones = clone TestUser::ascending('id')->all();

		foreach($clones as $clone)
		{
			$clone->save();
		}

		$this->assertEquals(($count * 2), TestUser::count());

		$users = TestUser::ascending('id')->all();

		$chunkedUsers = $users->chunk(3);

		foreach($chunkedUsers[0] as $key => $user)
		{
			$this->assertNotEquals($user->id, $chunkedUsers[1][$key]->id);

			$this->assertEquals($user->created_at, $chunkedUsers[1][$key]->created_at);

			$this->assertEquals($user->username, $chunkedUsers[1][$key]->username);

			$this->assertEquals($user->email, $chunkedUsers[1][$key]->email);
		}
	}

	/**
	 *
	 */

	public function testScoped()
	{
		$users = TestUserScoped::withArticles()->all();

		$this->assertEquals(2, count($users));
	}

	/**
	 *
	 */

	public function testDateTime()
	{
		$user = TestUserDateTime::get(1);

		$this->assertInstanceOf('\mako\chrono\Time', $user->created_at);
	}

	/**
	 *
	 */

	public function testUUIDKey()
	{
		$uuid = UUIDKey::create(['value' => 'foo']);

		$this->assertTrue(UUID::validate($uuid->id));

		$uuid = UUIDKey::first();

		$this->assertTrue(UUID::validate($uuid->id));
	}

	/**
	 *
	 */

	public function testCustomKey()
	{
		$custom = CustomKey::create(['value' => 'foo']);

		$this->assertEquals('foobarbax', $custom->id);

		$custom = CustomKey::first();

		$this->assertEquals('foobarbax', $custom->id);
	}

	/**
	 *
	 */

	public function testNoKey()
	{
		$none = NoKey::create(['value' => 'foo']);

		$columns = $none->getRawColumns();

		$this->assertTrue(empty($columns['id']));

		$none = NoKey::first();

		$columns = $none->getRawColumns();

		$this->assertTrue(empty($columns['id']));
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

	public function testQueryForwarding()
	{
		$user = TestUser::where('id', '=', 1)->first();

		$this->assertInstanceOf('mako\tests\integration\database\midgard\TestUser', $user);
	}

	/**
	 *
	 */

	public function testIncrement()
	{
		Counter::increment('counter');

		$counter = Counter::get(1);

		$before = $counter->counter;

		$counter->increment('counter');

		$connection = $counter->getConnection();

		$this->assertTrue($counter->counter === $before + 1);

		$this->assertFalse($counter->isModified());

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" + 1', $connection->getLog()[0]['query']);

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" + 1 WHERE "id" = \'1\'', $connection->getLog()[2]['query']);
	}

	/**
	 *
	 */

	public function testDecrement()
	{
		Counter::decrement('counter');

		$counter = Counter::get(1);

		$before = $counter->counter;

		$counter->decrement('counter');

		$connection = $counter->getConnection();

		$this->assertTrue($counter->counter === $before - 1);

		$this->assertFalse($counter->isModified());

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" - 1', $connection->getLog()[0]['query']);

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" - 1 WHERE "id" = \'1\'', $connection->getLog()[2]['query']);
	}

	/**
	 *
	 */

	public function testIncrement10()
	{
		Counter::increment('counter', 10);

		$counter = Counter::get(1);

		$before = $counter->counter;

		$counter->increment('counter', 10);

		$connection = $counter->getConnection();

		$this->assertTrue($counter->counter === $before + 10);

		$this->assertFalse($counter->isModified());

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" + 10', $connection->getLog()[0]['query']);

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" + 10 WHERE "id" = \'1\'', $connection->getLog()[2]['query']);
	}

	/**
	 *
	 */

	public function testDecrement10()
	{
		Counter::decrement('counter', 10);

		$counter = Counter::get(1);

		$before = $counter->counter;

		$counter->decrement('counter', 10);

		$connection = $counter->getConnection();

		$this->assertTrue($counter->counter === $before - 10);

		$this->assertFalse($counter->isModified());

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" - 10', $connection->getLog()[0]['query']);

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" - 10 WHERE "id" = \'1\'', $connection->getLog()[2]['query']);
	}
}