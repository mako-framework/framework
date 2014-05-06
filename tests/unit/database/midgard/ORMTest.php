<?php

namespace mako\tests\unit\database\midgard;

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
	protected $enableLocking = true;

	protected $readOnly = true;

	protected function set_array($array)
	{
		return json_encode($array);
	}

	public function get_array($json)
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

	protected $columns = ['username' => 'foo', 'password' => 'bar', 'array' => '[1,2,3]'];

	public function get_array($json)
	{
		return json_decode($json, true);
	}
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
		$user = new TestUser1([], true, false, true);

		$user->setLockVersion(404);

		$this->assertFalse($user->getLockVersion());

		//

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

	public function testGetColumns()
	{
		$columns = ['id' => '1', 'username' => 'foo'];

		$user = new TestUser1($columns);

		$this->assertEquals($columns, $user->getColumns());
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

		$this->assertEquals([1, 2, 3], $user->getColumn('array', true));

		//

		$user = new TestUser1();

		$user->setColumn('array', [1, 2, 3]);

		$this->assertEquals([1, 2, 3], $user->array);

		$this->assertEquals([1, 2, 3], $user->getColumn('array'));

		$this->assertEquals([1, 2, 3], $user->getColumn('array', true));

		//

		$user = new TestUser2();

		$user->array = [1, 2, 3];

		$this->assertEquals([1, 2, 3], $user->array);

		$this->assertEquals([1, 2, 3], $user->getColumn('array'));

		$this->assertEquals('[1,2,3]', $user->getColumn('array', true));

		//

		$user = new TestUser2();

		$user->setColumn('array', [1, 2, 3]);

		$this->assertEquals([1, 2, 3], $user->array);

		$this->assertEquals([1, 2, 3], $user->getColumn('array'));

		$this->assertEquals('[1,2,3]', $user->getColumn('array', true));

		//

		$user = new TestUser2();

		$user->setColumn('array', [1, 2, 3], true);

		$this->assertEquals([1, 2, 3], $user->getColumn('array', true));

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

		$this->assertEquals(['username' => 'foo', 'email' => 'bar'], $user->getColumns());

		//

		$user = new TestUser3(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1]);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar'], $user->getColumns());

		//

		$user = new TestUser3();

		$user->assign(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], false, false);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], $user->getColumns());

		//

		$user = new TestUser3(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], false, false);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], $user->getColumns());

		//

		$user = new TestUser3([], false, true, true);

		$user->assign(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], false, false);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1], $user->getColumns());

		//

		$user = new TestUser3(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], false, false, true);

		$this->assertEquals(['username' => 'foo', 'email' => 'bar', 'is_admin' => 1, 'id' => 1], $user->getColumns());
	}

	/**
	 * 
	 */

	public function testToArray()
	{
		$user = new TestUser4();

		$this->assertEquals(['username' => 'foo', 'array' => [1, 2, 3]], $user->toArray());

		$this->assertEquals(['username' => 'foo', 'password' => 'bar', 'array' => [1, 2, 3]], $user->toArray(false));

		$this->assertEquals(['username' => 'foo', 'password' => 'bar', 'array' => '[1,2,3]'], $user->toArray(false, true));
	}

	/**
	 * 
	 */

	public function testToJson()
	{
		$user = new TestUser4();

		$this->assertEquals('{"username":"foo","array":[1,2,3]}', $user->toJson());

		$this->assertEquals('{"username":"foo","password":"bar","array":[1,2,3]}', $user->toJson(false));

		$this->assertEquals('{"username":"foo","password":"bar","array":"[1,2,3]"}', $user->toJson(false, true));
	}

	/**
	 * 
	 */

	public function testToString()
	{
		$user = new TestUser4();

		$this->assertEquals('{"username":"foo","array":[1,2,3]}', (string) $user);
	}
}