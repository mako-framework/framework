<?php

namespace mako\tests\unit\database\midgard;

use \DateTime;

use \Mockery as m;

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

class TestUser1 extends \mako\database\midgard\ORM
{
	protected $including = ['profile'];

	protected $primaryKey = 'id';

	protected $tableName = 'users';
}

class TestUser2 extends TestUser1
{
	use \mako\database\midgard\traits\OptimisticLockingTrait;

	protected $readOnly = true;

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
		return new FakeRelation();
	}
}

class TestUser3 extends TestUser1
{
	protected $assignable = ['username', 'email'];
}

class TestUser4 extends TestUser1
{
	protected $protected = ['password'];

	protected $columns = ['username' => 'foo', 'password' => 'bar', 'array' => '[1,2,3]', 'optional' => null];

	public function arrayAccessor($json)
	{
		return json_decode($json, true);
	}
}

class Testuser5 extends TestUser1
{
	protected static $dateFormat = 'Y-m-d H:i:s';

	protected $cast = ['created_at' => 'date'];

	protected function getDateFormat()
	{
		return 'Y-m-d H:i:s';
	}
}

class ORMTestApple extends \mako\database\midgard\ORM
{

}

class TestCastingScalars extends \mako\database\midgard\ORM
{
	protected $cast = ['boolean' => 'boolean', 'integer' => 'integer', 'float' => 'float'];
}

class TestCastingDate extends \mako\database\midgard\ORM
{
	protected $cast = ['date' => 'date'];
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class ORMTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function testGetTableName()
	{
		$user = new TestUser1();

		$this->assertEquals('users', $user->getTable());
	}

	/**
	 *
	 */

	public function testGetPrimaryKey()
	{
		$user = new TestUser1();

		$this->assertEquals('id', $user->getPrimaryKey());
	}

	/**
	 *
	 */

	public function testGetPrimaryKeyValue()
	{
		$user = new TestUser1(['id' => '1']);

		$this->assertEquals('1', $user->getPrimaryKeyValue());
	}

	/**
	 *
	 */

	public function testGetForeignKey()
	{
		$user = new TestUser1();

		$this->assertEquals('testuser1_id', $user->getForeignKey());
	}

	/**
	 *
	 */

	public function testGetClass()
	{
		$user = new TestUser1();

		$this->assertEquals('\mako\tests\unit\database\midgard\TestUser1', $user->getClass());
	}

	/**
	 *
	 */

	public function testSetAndGetLockVersion()
	{
		$user = new TestUser2([], true, false, true);

		$user->setLockVersion(404);

		$this->assertEquals(404, $user->getLockVersion());
	}

	/**
	 *
	 */

	public function testIsReadOnly()
	{
		$user = new TestUser1();

		$this->assertFalse($user->isReadOnly());

		//

		$user = new TestUser2();

		$this->assertTrue($user->isReadOnly());
	}

	/**
	 *
	 */

	public function testSetAndGetIncludes()
	{
		$user = new TestUser1();

		$this->assertEquals(['profile'], $user->getIncludes());

		$user->setIncludes(['profile', 'profile.comments']);

		$this->assertEquals(['profile', 'profile.comments'], $user->getIncludes());
	}

	/**
	 *
	 */

	public function testSetAndGetRelated()
	{
		$user = new TestUser1();

		$this->assertEmpty($user->getRelated());

		$user->setRelated('profile', 'the profile object');

		$this->assertEquals(['profile' => 'the profile object'], $user->getRelated());
	}

	/**
	 *
	 */

	public function testGetRawColumns()
	{
		$columns = ['id' => '1', 'username' => 'foo'];

		$user = new TestUser1($columns);

		$this->assertEquals($columns, $user->getRawColumns());
	}

	/**
	 *
	 */

	public function testSetandGetColumn()
	{
		$user = new TestUser1();

		$user->array = [1, 2, 3];

		$this->assertEquals([1, 2, 3], $user->array);

		$this->assertEquals([1, 2, 3], $user->getColumn('array'));

		$this->assertEquals([1, 2, 3], $user->getRawColumn('array'));

		//

		$user = new TestUser1();

		$user->setColumn('array', [1, 2, 3]);

		$this->assertEquals([1, 2, 3], $user->array);

		$this->assertEquals([1, 2, 3], $user->getColumn('array'));

		$this->assertEquals([1, 2, 3], $user->getRawColumn('array'));

		//

		$user = new TestUser2();

		$user->array = [1, 2, 3];

		$this->assertEquals([1, 2, 3], $user->array);

		$this->assertEquals([1, 2, 3], $user->getColumn('array'));

		$this->assertEquals('[1,2,3]', $user->getRawColumn('array'));

		//

		$user = new TestUser2();

		$user->setColumn('array', [1, 2, 3]);

		$this->assertEquals([1, 2, 3], $user->array);

		$this->assertEquals([1, 2, 3], $user->getColumn('array'));

		$this->assertEquals('[1,2,3]', $user->getRawColumn('array'));

		//

		$user = new TestUser2();

		$user->setRawColumn('array', [1, 2, 3]);

		$this->assertEquals([1, 2, 3], $user->getRawColumn('array'));

		//

		$user = new TestUser2();

		$this->assertEquals('fake relation', $user->fake_relation);
	}

	/**
	 *
	 */

	public function testAssign()
	{
		$user = new TestUser3();

		$user->assign(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1]);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar'], $user->getRawColumns());

		//

		$user = new TestUser3(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1]);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar'], $user->getRawColumns());

		//

		$user = new TestUser3();

		$user->assign(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], false, false);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], $user->getRawColumns());

		//

		$user = new TestUser3(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], false, false);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], $user->getRawColumns());

		//

		$user = new TestUser3([], false, true, true);

		$user->assign(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], false, false);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1], $user->getRawColumns());

		//

		$user = new TestUser3(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], false, false, true);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], $user->getRawColumns());
	}

	/**
	 *
	 */

	public function testTableNameGuessing()
	{
		$apple = new ORMTestApple;

		$this->assertEquals('ormtest_apples', $apple->getTable());
	}

	/**
	 *
	 */

	public function testDateTimeColumns()
	{
		$user = new TestUser5(['created_at' => '2014-02-01 13:10:32'], true, false, true);

		$this->assertInstanceOf('\mako\chrono\Time', $user->created_at);
	}

	/**
	 *
	 */

	public function testGetColumnWithNullValue()
	{
		$user = new TestUser4();

		$this->assertNull($user->optional);

		$this->assertNull($user->getRawColumn('optional'));
	}

	/**
	 *
	 */

	public function testIsModified()
	{
		$user = new TestUser1(['foo' => 123, 'bar' => 456], true, false, true);

		$this->assertFalse($user->isModified());

		$user->foo = 789;

		$this->assertTrue($user->isModified());
	}

	/**
	 *
	 */

	public function testGetModified()
	{
		$user = new TestUser1(['foo' => 123, 'bar' => 456], true, false, true);

		$this->assertEquals([], $user->getModified());

		$user->foo = 789;

		$this->assertEquals(['foo' => 789], $user->getModified());
	}

	/**
	 *
	 */

	public function testCastingScalars()
	{
		$cast = new TestCastingScalars;

		$cast->boolean  = 1;
		$cast->integer  = '1';
		$cast->float    = '1.1';

		$this->assertInternalType('boolean', $cast->boolean);

		$this->assertSame(true, $cast->boolean);

		$this->assertInternalType('integer', $cast->integer);

		$this->assertSame(1, $cast->integer);

		$this->assertInternalType('float', $cast->float);

		$this->assertSame(1.1, $cast->float);

		//

		$cast = new TestCastingScalars;

		$cast->boolean  = 0;
		$cast->integer  = '1';
		$cast->float    = '1.1';

		$this->assertInternalType('boolean', $cast->boolean);

		$this->assertSame(false, $cast->boolean);

		$this->assertInternalType('integer', $cast->integer);

		$this->assertSame(1, $cast->integer);

		$this->assertInternalType('float', $cast->float);

		$this->assertSame(1.1, $cast->float);

		//

		$cast = new TestCastingScalars(['boolean' => '1', 'integer' => '1', 'float' => '1.13'], true, false, true);

		$this->assertInternalType('boolean', $cast->boolean);

		$this->assertSame(true, $cast->boolean);

		$this->assertInternalType('integer', $cast->integer);

		$this->assertSame(1, $cast->integer);

		$this->assertInternalType('float', $cast->float);

		$this->assertSame(1.13, $cast->float);

		//

		$cast = new TestCastingScalars(['boolean' => '0', 'integer' => '1', 'float' => '1'], true, false, true);

		$this->assertInternalType('boolean', $cast->boolean);

		$this->assertSame(false, $cast->boolean);

		$this->assertInternalType('integer', $cast->integer);

		$this->assertSame(1, $cast->integer);

		$this->assertInternalType('float', $cast->float);

		$this->assertSame(1.0, $cast->float);

		//

		$cast = new TestCastingScalars(['boolean' => 't'], true, false, true);
		$this->assertInternalType('boolean', $cast->boolean);
		$this->assertSame(true, $cast->boolean);

		$cast = new TestCastingScalars(['boolean' => 'f'], true, false, true);
		$this->assertInternalType('boolean', $cast->boolean);
		$this->assertSame(false, $cast->boolean);
	}

	/**
	 *
	 */

	public function testCastingDate()
	{
		$cast = new TestCastingDate;

		$cast->date = new DateTime;

		$this->assertInstanceOf('DateTime', $cast->date);

		//

		$cast = m::mock('mako\tests\unit\database\midgard\TestCastingDate');

		$cast->shouldAllowMockingProtectedMethods();

		$cast->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');

		$cast->makePartial();

		$cast->date = '2014-01-01 12:12:12';

		$this->assertInstanceOf('DateTime', $cast->date);
	}

	/**
	 *
	 */

	public function testToArray()
	{
		$user = new TestUser4();

		$this->assertEquals(['username' => 'foo', 'array' => [1, 2, 3], 'optional' => null], $user->toArray());

		$this->assertEquals(['username' => 'foo', 'password' => 'bar', 'array' => [1, 2, 3], 'optional' => null], $user->toArray(false));

		$user = new TestUser5(['created_at' => '2014-02-01 13:10:32'], true, false, true);

		$this->assertEquals(['created_at' => '2014-02-01 13:10:32'], $user->toArray());
	}

	/**
	 *
	 */

	public function testToJson()
	{
		$user = new TestUser4();

		$this->assertEquals('{"username":"foo","array":[1,2,3],"optional":null}', $user->toJson());

		$this->assertEquals('{"username":"foo","password":"bar","array":[1,2,3],"optional":null}', $user->toJson(false));

		$user = new TestUser5(['created_at' => '2014-02-01 13:10:32'], true, false, true);

		$this->assertEquals('{"created_at":"2014-02-01 13:10:32"}', $user->toJson());
	}

	/**
	 *
	 */

	public function testToString()
	{
		$user = new TestUser4();

		$this->assertEquals('{"username":"foo","array":[1,2,3],"optional":null}', (string) $user);

		$user = new TestUser5(['created_at' => '2014-02-01 13:10:32'], true, false, true);

		$this->assertEquals('{"created_at":"2014-02-01 13:10:32"}', (string) $user);
	}
}