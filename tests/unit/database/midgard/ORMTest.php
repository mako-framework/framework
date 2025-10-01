<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\midgard;

use DateTime;
use mako\chrono\Time;
use mako\database\midgard\ORM;
use mako\database\midgard\traits\OptimisticLockingTrait;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class FakeRelation
{
	public function getRelated()
	{
		return 'fake relation';
	}
}

class TestUser1 extends ORM
{
	protected array $including = ['profile'];

	protected string $primaryKey = 'id';

	protected string $tableName = 'users';
}

class TestUser2 extends TestUser1
{
	use OptimisticLockingTrait;

	protected function arrayMutator($array)
	{
		return json_encode($array);
	}

	public function arrayAccessor($json)
	{
		return json_decode($json, true);
	}

	public function fake_relation()
	{
		return new FakeRelation;
	}
}

class TestUser3 extends TestUser1
{
	protected string $foreignKeyName = 'testuser3';

	protected array $assignable = ['username', 'email'];
}

class TestUser4 extends TestUser1
{
	protected array $protected = ['password'];

	protected array $columns = ['username' => 'foo', 'password' => 'bar', 'array' => '[1,2,3]', 'optional' => null];

	public function arrayAccessor($json)
	{
		return json_decode($json, true);
	}
}

class Testuser5 extends TestUser1
{
	protected static $dateFormat = 'Y-m-d H:i:s';

	protected array $cast = ['created_at' => 'date'];

	protected function getDateFormat(): string
	{
		return 'Y-m-d H:i:s';
	}
}

class ORMTestApple extends ORM
{

}

class TestCastingScalars extends ORM
{
	protected array $cast = ['boolean' => 'bool', 'integer' => 'int', 'float' => 'float'];
}

class TestCastingDate extends ORM
{
	protected array $cast = ['date' => 'date'];
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('unit')]
class ORMTest extends TestCase
{
	/**
	 *
	 */
	public function testGetTableName(): void
	{
		$user = new TestUser1;

		$this->assertEquals('users', $user->getTable());
	}

	/**
	 *
	 */
	public function testGetPrimaryKey(): void
	{
		$user = new TestUser1;

		$this->assertEquals('id', $user->getPrimaryKey());
	}

	/**
	 *
	 */
	public function testGetPrimaryKeyValue(): void
	{
		$user = new TestUser1(['id' => '1']);

		$this->assertEquals('1', $user->getPrimaryKeyValue());
	}

	/**
	 *
	 */
	public function testGetForeignKey(): void
	{
		$user = new TestUser1;

		$this->assertEquals('test_user1_id', $user->getForeignKey());

		$user = new TestUser3;

		$this->assertEquals('testuser3', $user->getForeignKey());
	}

	/**
	 *
	 */
	public function testGetClass(): void
	{
		$user = new TestUser1;

		$this->assertEquals('\\' . TestUser1::class, $user->getClass());
	}

	/**
	 *
	 */
	public function testSetAndGetLockVersion(): void
	{
		$user = new TestUser2([], true, false, true);

		$user->setLockVersion(404);

		$this->assertEquals(404, $user->getLockVersion());
	}

	/**
	 *
	 */
	public function testSetAndGetIncludes(): void
	{
		$user = new TestUser1;

		$this->assertEquals(['profile'], $user->getIncludes());

		$user->setIncludes(['profile', 'profile.comments']);

		$this->assertEquals(['profile', 'profile.comments'], $user->getIncludes());
	}

	/**
	 *
	 */
	public function testSetAndGetRelated(): void
	{
		$user = new TestUser1;

		$this->assertEmpty($user->getRelated());

		$user->setRelated('profile', 'the profile object');

		$this->assertEquals(['profile' => 'the profile object'], $user->getRelated());
	}

	/**
	 *
	 */
	public function testGetRawColumnValues(): void
	{
		$columns = ['id' => '1', 'username' => 'foo'];

		$user = new TestUser1($columns);

		$this->assertEquals($columns, $user->getRawColumnValues());
	}

	/**
	 *
	 */
	public function testSetandGetColumn(): void
	{
		$user = new TestUser1;

		$user->array = [1, 2, 3];

		$this->assertEquals([1, 2, 3], $user->array);

		$this->assertEquals([1, 2, 3], $user->getValue('array'));

		$this->assertEquals([1, 2, 3], $user->getColumnValue('array'));

		$this->assertEquals([1, 2, 3], $user->getRawColumnValue('array'));

		//

		$user = new TestUser1;

		$user->setColumnValue('array', [1, 2, 3]);

		$this->assertEquals([1, 2, 3], $user->array);

		$this->assertEquals([1, 2, 3], $user->getValue('array'));

		$this->assertEquals([1, 2, 3], $user->getColumnValue('array'));

		$this->assertEquals([1, 2, 3], $user->getRawColumnValue('array'));

		//

		$user = new TestUser2;

		$user->array = [1, 2, 3];

		$this->assertEquals([1, 2, 3], $user->array);

		$this->assertEquals([1, 2, 3], $user->getValue('array'));

		$this->assertEquals([1, 2, 3], $user->getColumnValue('array'));

		$this->assertEquals('[1,2,3]', $user->getRawColumnValue('array'));

		//

		$user = new TestUser2;

		$user->setColumnValue('array', [1, 2, 3]);

		$this->assertEquals([1, 2, 3], $user->array);

		$this->assertEquals([1, 2, 3], $user->getValue('array'));

		$this->assertEquals([1, 2, 3], $user->getColumnValue('array'));

		$this->assertEquals('[1,2,3]', $user->getRawColumnValue('array'));

		//

		$user = new TestUser2;

		$user->setRawColumnValue('array', [1, 2, 3]);

		$this->assertEquals([1, 2, 3], $user->getRawColumnValue('array'));

		//

		$user = new TestUser2;

		$this->assertEquals('fake relation', $user->fake_relation);
	}

	/**
	 *
	 */
	public function testAssign(): void
	{
		$user = new TestUser3;

		$user->assign(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1]);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar'], $user->getRawColumnValues());

		//

		$user = new TestUser3(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1]);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar'], $user->getRawColumnValues());

		//

		$user = new TestUser3;

		$user->assign(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], false, false);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], $user->getRawColumnValues());

		//

		$user = new TestUser3(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], false, false);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], $user->getRawColumnValues());

		//

		$user = new TestUser3([], false, true, true);

		$user->assign(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], false, false);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1], $user->getRawColumnValues());

		//

		$user = new TestUser3(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], false, false, true);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], $user->getRawColumnValues());
	}

	/**
	 *
	 */
	public function testTableNameGuessing(): void
	{
		$apple = new ORMTestApple;

		$this->assertEquals('ormtest_apples', $apple->getTable());
	}

	/**
	 *
	 */
	public function testDateTimeColumns(): void
	{
		$user = new TestUser5(['created_at' => '2014-02-01 13:10:32'], true, false, true);

		$this->assertInstanceOf(Time::class, $user->created_at);
	}

	/**
	 *
	 */
	public function testGetColumnWithNullValue(): void
	{
		$user = new TestUser4;

		$this->assertNull($user->optional);

		$this->assertNull($user->getValue('optional'));

		$this->assertNull($user->getColumnValue('optional'));

		$this->assertNull($user->getRawColumnValue('optional'));
	}

	/**
	 *
	 */
	public function testIsModified(): void
	{
		$user = new TestUser1(['foo' => 123, 'bar' => 456], true, false, true);

		$this->assertFalse($user->isModified());

		$user->foo = 789;

		$this->assertTrue($user->isModified());
	}

	/**
	 *
	 */
	public function testGetModified(): void
	{
		$user = new TestUser1(['foo' => 123, 'bar' => 456], true, false, true);

		$this->assertEquals([], $user->getModified());

		$user->foo = 789;

		$this->assertEquals(['foo' => 789], $user->getModified());
	}

	/**
	 *
	 */
	public function testGetModifiedWithNullValues(): void
	{
		$user = new TestUser1(['foo' => null], true, false, true);

		$this->assertEquals([], $user->getModified());

		$user->foo = 789;

		$this->assertEquals(['foo' => 789], $user->getModified());
	}

	/**
	 *
	 */
	public function testCastingScalars(): void
	{
		$cast = new TestCastingScalars;

		$cast->boolean  = 1;
		$cast->integer  = '1';
		$cast->float    = '1.1';

		$this->assertIsBool($cast->boolean);

		$this->assertSame(true, $cast->boolean);

		$this->assertIsInt($cast->integer);

		$this->assertSame(1, $cast->integer);

		$this->assertIsFloat($cast->float);

		$this->assertSame(1.1, $cast->float);

		//

		$cast = new TestCastingScalars;

		$cast->boolean  = 0;
		$cast->integer  = '1';
		$cast->float    = '1.1';

		$this->assertIsBool($cast->boolean);

		$this->assertSame(false, $cast->boolean);

		$this->assertIsInt($cast->integer);

		$this->assertSame(1, $cast->integer);

		$this->assertIsFloat($cast->float);

		$this->assertSame(1.1, $cast->float);

		//

		$cast = new TestCastingScalars(['boolean' => '1', 'integer' => '1', 'float' => '1.13'], true, false, true);

		$this->assertIsBool($cast->boolean);

		$this->assertSame(true, $cast->boolean);

		$this->assertIsInt($cast->integer);

		$this->assertSame(1, $cast->integer);

		$this->assertIsFloat($cast->float);

		$this->assertSame(1.13, $cast->float);

		//

		$cast = new TestCastingScalars(['boolean' => '0', 'integer' => '1', 'float' => '1'], true, false, true);

		$this->assertIsBool($cast->boolean);

		$this->assertSame(false, $cast->boolean);

		$this->assertIsInt($cast->integer);

		$this->assertSame(1, $cast->integer);

		$this->assertIsFloat($cast->float);

		$this->assertSame(1.0, $cast->float);

		//

		$cast = new TestCastingScalars(['boolean' => 't'], true, false, true);
		$this->assertIsBool($cast->boolean);
		$this->assertSame(true, $cast->boolean);

		$cast = new TestCastingScalars(['boolean' => 'f'], true, false, true);
		$this->assertIsBool($cast->boolean);
		$this->assertSame(false, $cast->boolean);
	}

	/**
	 *
	 */
	public function testCastingDate(): void
	{
		$cast = new TestCastingDate;

		$cast->date = new DateTime;

		$this->assertInstanceOf(DateTime::class, $cast->date);

		//

		$cast = Mockery::mock(TestCastingDate::class);

		$cast->shouldAllowMockingProtectedMethods();

		$cast->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');

		$cast->makePartial();

		$cast->date = '2014-01-01 12:12:12';

		$this->assertInstanceOf(DateTime::class, $cast->date);
	}

	/**
	 *
	 */
	public function testToArray(): void
	{
		$user = new TestUser4;

		$this->assertEquals(['username' => 'foo', 'array' => [1, 2, 3], 'optional' => null], $user->toArray());

		$this->assertEquals(['username' => 'foo', 'password' => 'bar', 'array' => [1, 2, 3], 'optional' => null], $user->protect(false)->toArray());

		$user = new TestUser5(['created_at' => '2014-02-01 13:10:32'], true, false, true);

		$this->assertEquals(['created_at' => '2014-02-01T13:10:32+00:00'], $user->toArray());
	}

	/**
	 *
	 */
	public function testJsonSerialize(): void
	{
		$user = new TestUser4;

		$this->assertEquals('{"username":"foo","array":[1,2,3],"optional":null}', json_encode($user));

		$user = new TestUser5(['created_at' => '2014-02-01 13:10:32'], true, false, true);

		$this->assertEquals('{"created_at":"2014-02-01T13:10:32+00:00"}', json_encode($user));
	}

	/**
	 *
	 */
	public function testToJson(): void
	{
		$user = new TestUser4;

		$this->assertEquals('{"username":"foo","array":[1,2,3],"optional":null}', $user->toJson());

		$this->assertEquals('{"username":"foo","password":"bar","array":[1,2,3],"optional":null}', $user->protect(false)->toJson());

		$user = new TestUser5(['created_at' => '2014-02-01 13:10:32'], true, false, true);

		$this->assertEquals('{"created_at":"2014-02-01T13:10:32+00:00"}', $user->toJson());
	}

	/**
	 *
	 */
	public function testToString(): void
	{
		$user = new TestUser4;

		$this->assertEquals('{"username":"foo","array":[1,2,3],"optional":null}', (string) $user);

		$user = new TestUser5(['created_at' => '2014-02-01 13:10:32'], true, false, true);

		$this->assertEquals('{"created_at":"2014-02-01T13:10:32+00:00"}', (string) $user);
	}
}
