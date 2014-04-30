<?php

namespace mako\tests\unit\database\midgard;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

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
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

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
}