<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard;

use DateTime;
use LogicException;
use mako\database\exceptions\NotFoundException;
use mako\database\midgard\relations\HasOne;
use mako\database\midgard\ResultSet;
use mako\database\query\Subquery;
use mako\tests\integration\ORMTestCase;
use mako\tests\integration\TestORM;
use mako\utility\UUID;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class TestUser extends TestORM
{
	protected string $tableName = 'users';

	public function profile(): HasOne
	{
		return $this->hasOne(Profile::class, 'user_id');
	}
}

class Profile extends TestORM
{
	protected string $tableName = 'profiles';
}

class TestUserScoped extends TestUser
{
	public function withArticlesScope($query): void
	{
		$query->join('articles', 'articles.user_id', '=', 'users.id')->distinct();
	}
}

class TestUserDateTime extends TestUser
{
	protected array $cast = ['created_at' => 'date'];
}

class UUIDKey extends TestORM
{
	protected string $tableName = 'uuid_keys';

	protected int $primaryKeyType = TestORM::PRIMARY_KEY_TYPE_UUID;
}

class CustomKey extends TestORM
{
	protected string $tableName = 'custom_keys';

	protected int $primaryKeyType = TestORM::PRIMARY_KEY_TYPE_CUSTOM;

	protected function generatePrimaryKey(): mixed
	{
		return 'foobarbax';
	}
}

class NoKey extends TestORM
{
	protected string $tableName = 'no_keys';

	protected int $primaryKeyType = TestORM::PRIMARY_KEY_TYPE_NONE;
}

class Counter extends TestORM
{
	protected string $tableName = 'counters';
}

enum FooEnum: int
{
	case ONE = 1;
	case TWO = 2;
}

class Enum extends TestORM
{
	protected array $cast = ['value' => ['enum' => FooEnum::class]];
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('integration')]
#[Group('integration:database')]
#[RequiresPhpExtension('pdo')]
#[RequiresPhpExtension('pdo_sqlite')]
class ORMTest extends ORMTestCase
{
	/**
	 *
	 */
	public function testGet(): void
	{
		$user = TestUser::get(1);

		$this->assertInstanceOf(TestUser::class, $user);

		$this->assertEquals(1, $user->id);

		$this->assertEquals('2014-04-30 14:40:01', $user->created_at);

		$this->assertEquals('foo', $user->username);

		$this->assertEquals('foo@example.org', $user->email);
	}

	/**
	 *
	 */
	public function testGetOrThrow(): void
	{
		$this->expectException(NotFoundException::class);

		TestUser::getOrThrow(1000);
	}

	/**
	 *
	 */
	public function testGetOrThrowWithCustomException(): void
	{
		$this->expectException(LogicException::class);

		TestUser::getOrThrow(1000, [], LogicException::class);
	}

	/**
	 *
	 */
	public function testGetOrThrowWithEagerLoading(): void
	{
		$user = (new TestUser)->including(['profile'])->getOrThrow(1);

		$queries = $user->getConnection()->getLog();

		$this->assertSame(2, count($queries));

		$this->assertSame('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $queries[0]['query']);

		$this->assertSame('SELECT * FROM "profiles" WHERE "profiles"."user_id" IN (1)', $queries[1]['query']);
	}

	/**
	 *
	 */
	public function testEagerLoadingAliasing(): void
	{
		$user = (new TestUser)->including(['profile as eliforp'])->getOrThrow(1);

		$queries = $user->getConnection()->getLog();

		$this->assertSame(2, count($queries));

		$this->assertSame('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $queries[0]['query']);

		$this->assertSame('SELECT * FROM "profiles" WHERE "profiles"."user_id" IN (1)', $queries[1]['query']);

		$this->assertTrue(empty($user->getRelated()['profile']));

		$this->assertFalse(empty($user->getRelated()['eliforp']));
	}

	/**
	 *
	 */
	public function testGetNonExistent(): void
	{
		$user = TestUser::get(999);

		$this->assertNull($user);
	}

	/**
	 *
	 */
	public function testFirst(): void
	{
		$users = (new TestUser)->first();

		$this->assertInstanceOf(TestUser::class, $users);
	}

	/**
	 *
	 */
	public function testFirstOrThrow(): void
	{
		$this->expectException(NotFoundException::class);

		(new TestUser)->getQuery()->where('id', '=', 1000)->firstOrThrow();
	}

	/**
	 *
	 */
	public function testFirstOrThrowWithCustomException(): void
	{
		$this->expectException(LogicException::class);

		(new TestUser)->getQuery()->where('id', '=', 1000)->firstOrThrow(LogicException::class);
	}

	/**
	 *
	 */
	public function testAll(): void
	{
		$users = (new TestUser)->all();

		$this->assertInstanceOf(ResultSet::class, $users);

		foreach ($users as $user) {
			$this->assertInstanceOf(TestUser::class, $user);
		}
	}

	/**
	 *
	 */
	public function testYield(): void
	{
		$users = (new TestUser)->yield();

		$this->assertInstanceOf('Generator', $users);

		foreach ($users as $user) {
			$this->assertInstanceOf(TestUser::class, $user);
		}
	}

	/**
	 *
	 */
	public function testLimitColumnsFirst(): void
	{
		$user = (new TestUser)->select(['username', 'email'])->where('id', '=', 1)->first();

		$this->assertEquals(['username' => 'foo', 'email' => 'foo@example.org'], $user->getRawColumnValues());
	}

	/**
	 *
	 */
	public function testLimitColumnsAll(): void
	{
		$users = (new TestUser)->select(['username', 'email'])->all();

		$this->assertEquals(['username' => 'foo', 'email' => 'foo@example.org'], $users[0]->getRawColumnValues());
	}

	/**
	 *
	 */
	public function testJoin(): void
	{
		$users = (new TestUser)->join('articles', 'articles.user_id', '=', 'users.id')->distinct()->all();

		$this->assertEquals(2, count($users));

		$this->assertEquals(['id' => 1, 'created_at' => '2014-04-30 14:40:01', 'username' => 'foo', 'email' => 'foo@example.org'], $users[0]->getRawColumnValues());

		$this->assertEquals(['id' => 2, 'created_at' => '2014-04-30 14:02:43', 'username' => 'bar', 'email' => 'bar@example.org'], $users[1]->getRawColumnValues());
	}

	/**
	 *
	 */
	public function testSave(): void
	{
		$dateTime = new DateTime;

		$user = new TestUser;

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
	public function testCreate(): void
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
	public function testUpdate(): void
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
	public function testDelete(): void
	{
		$dateTime = new DateTime;

		$user = TestUser::create(['username' => 'bax', 'email' => 'bax@example.org', 'created_at' => $dateTime]);

		$count = (new TestUser)->count();

		$user->delete();

		$this->assertEquals(($count - 1), (new TestUser)->count());
	}

	/**
	 *
	 */
	public function testClone(): void
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
	public function testCloneResultSet(): void
	{
		$count = (new TestUser)->count();

		$clones = clone (new TestUser)->ascending('id')->all();

		foreach ($clones as $clone) {
			$clone->save();
		}

		$this->assertEquals(($count * 2), (new TestUser)->count());

		$users = (new TestUser)->ascending('id')->all();

		$chunkedUsers = $users->chunk(3);

		foreach ($chunkedUsers[0] as $key => $user) {
			$this->assertNotEquals($user->id, $chunkedUsers[1][$key]->id);

			$this->assertEquals($user->created_at, $chunkedUsers[1][$key]->created_at);

			$this->assertEquals($user->username, $chunkedUsers[1][$key]->username);

			$this->assertEquals($user->email, $chunkedUsers[1][$key]->email);
		}
	}

	/**
	 *
	 */
	public function testScoped(): void
	{
		$users = (new TestUserScoped)->scope('withArticles')->all();

		$this->assertEquals(2, count($users));
	}

	/**
	 *
	 */
	public function testScopedSnakeCase(): void
	{
		$users = (new TestUserScoped)->scope('with_articles')->all();

		$this->assertEquals(2, count($users));
	}

	/**
	 *
	 */
	public function testDateTime(): void
	{
		$user = TestUserDateTime::get(1);

		$this->assertInstanceOf('\mako\chrono\Time', $user->created_at);
	}

	/**
	 *
	 */
	public function testUUIDKey(): void
	{
		$uuid = UUIDKey::create(['value' => 'foo']);

		$this->assertTrue(UUID::validate($uuid->id));

		$uuid = (new UUIDKey)->first();

		$this->assertTrue(UUID::validate($uuid->id));
	}

	/**
	 *
	 */
	public function testCustomKey(): void
	{
		$custom = CustomKey::create(['value' => 'foo']);

		$this->assertEquals('foobarbax', $custom->id);

		$custom = (new CustomKey)->first();

		$this->assertEquals('foobarbax', $custom->id);
	}

	/**
	 *
	 */
	public function testNoKey(): void
	{
		$none = NoKey::create(['value' => 'foo']);

		$columns = $none->getRawColumnValues();

		$this->assertTrue(empty($columns['id']));

		$none = (new NoKey)->first();

		$columns = $none->getRawColumnValues();

		$this->assertTrue(empty($columns['id']));
	}

	/**
	 *
	 */
	public function testToArray(): void
	{
		$user = TestUser::get(1)->toArray();

		$this->assertEquals(['id' => '1', 'created_at' => '2014-04-30 14:40:01', 'username' => 'foo', 'email' => 'foo@example.org'], $user);
	}

	/**
	 *
	 */
	public function testToJSON(): void
	{
		$user = TestUser::get(1)->toJson();

		$this->assertEquals('{"id":1,"created_at":"2014-04-30 14:40:01","username":"foo","email":"foo@example.org"}', $user);
	}

	/**
	 *
	 */
	public function testQueryForwarding(): void
	{
		$user = (new TestUser)->where('id', '=', 1)->first();

		$this->assertInstanceOf(TestUser::class, $user);
	}

	/**
	 *
	 */
	public function testIncrement(): void
	{
		(new Counter)->increment('counter');

		$counter = Counter::get(1);

		$before = $counter->counter;

		$counter->increment('counter');

		$connection = $counter->getConnection();

		$this->assertTrue($counter->counter === $before + 1);

		$this->assertFalse($counter->isModified());

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" + 1', $connection->getLog()[0]['query']);

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" + 1 WHERE "id" = 1', $connection->getLog()[2]['query']);
	}

	/**
	 *
	 */
	public function testDecrement(): void
	{
		(new Counter)->decrement('counter');

		$counter = Counter::get(1);

		$before = $counter->counter;

		$counter->decrement('counter');

		$connection = $counter->getConnection();

		$this->assertTrue($counter->counter === $before - 1);

		$this->assertFalse($counter->isModified());

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" - 1', $connection->getLog()[0]['query']);

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" - 1 WHERE "id" = 1', $connection->getLog()[2]['query']);
	}

	/**
	 *
	 */
	public function testIncrement10(): void
	{
		(new Counter)->increment('counter', 10);

		$counter = Counter::get(1);

		$before = $counter->counter;

		$counter->increment('counter', 10);

		$connection = $counter->getConnection();

		$this->assertTrue($counter->counter === $before + 10);

		$this->assertFalse($counter->isModified());

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" + 10', $connection->getLog()[0]['query']);

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" + 10 WHERE "id" = 1', $connection->getLog()[2]['query']);
	}

	/**
	 *
	 */
	public function testDecrement10(): void
	{
		(new Counter)->decrement('counter', 10);

		$counter = Counter::get(1);

		$before = $counter->counter;

		$counter->decrement('counter', 10);

		$connection = $counter->getConnection();

		$this->assertTrue($counter->counter === $before - 10);

		$this->assertFalse($counter->isModified());

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" - 10', $connection->getLog()[0]['query']);

		$this->assertEquals('UPDATE "counters" SET "counter" = "counter" - 10 WHERE "id" = 1', $connection->getLog()[2]['query']);
	}

	/**
	 *
	 */
	public function testToArrayWithNullRelatedRecord(): void
	{
		/** @var \mako\database\midgard\ORM $user */
		$user = new TestUser(['username' => 'foobar']);

		$user->setRelated('relation_1', null);

		$array = $user->toArray();

		$this->assertSame(['username' => 'foobar', 'relation_1' => null], $array);
	}

	/**
	 *
	 */
	public function testEnumCasting(): void
	{
		$enum = Enum::get(1);

		$this->assertIsObject($enum->value);

		$this->assertInstanceOf(FooEnum::class, $enum->value);

		$this->assertEquals(FooEnum::ONE, $enum->value);

		$enum->value = FooEnum::TWO;

		$enum->save();

		$enum = Enum::get(1);

		$this->assertIsObject($enum->value);

		$this->assertInstanceOf(FooEnum::class, $enum->value);

		$this->assertEquals(FooEnum::TWO, $enum->value);
	}

	/**
	 *
	 */
	public function testSubquery(): void
	{
		$counters = (new Counter)->in('id', new Subquery(function ($query): void {
			$query->table('counters')->select(['id']);
		}))
		->all();

		$this->assertSame(3, count($counters));
	}

	/**
	 *
	 */
	public function testInsertAndReturn(): void
	{
		$inserted = (new TestUser)->insertAndReturn([
			'created_at' => '2025-05-28 23:23:00',
			'username'   => 'bax',
			'email'      => 'bax@example.org',
		]);

		$this->assertInstanceOf(TestUser::class, $inserted);
		$this->assertIsInt($inserted->id);
		$this->assertSame('2025-05-28 23:23:00', $inserted->created_at);
		$this->assertSame('bax', $inserted->username);
		$this->assertSame('bax@example.org', $inserted->email);
	}

	/**
	 *
	 */
	public function testUpdateAndReturn(): void
	{
		$updated = (new TestUser)->where('id', '=', 1)->updateAndReturn(['username' => 'bax'], ['username']);

		$this->assertInstanceOf(ResultSet::class, $updated);
		$this->assertInstanceOf(TestUser::class, $updated[0]);

		$this->assertEquals(1, $updated[0]->id);
		$this->assertEquals('bax', $updated[0]->username);
		$this->assertSame(['id' => 1, 'username' => 'bax'], $updated[0]->toArray());
	}
}
