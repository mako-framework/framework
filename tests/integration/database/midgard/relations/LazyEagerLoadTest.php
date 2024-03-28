<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard\relations;

use mako\tests\integration\ORMTestCase;
use mako\tests\integration\TestORM;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class LazyHasManyUser extends TestORM
{
	protected string $tableName = 'users';

	public function articles()
	{
		return $this->hasMany(LazyHasManyArticle::class, 'user_id');
	}
}

class LazyHasManyArticle extends TestORM
{
	protected string $tableName = 'articles';
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
class LazyEagerLoadTest extends ORMTestCase
{
	/**
	 *
	 */
	public function testIncludeOnModel(): void
	{
		$user = LazyHasManyUser::get(1);

		$this->assertFalse($user->includes('articles'));

		$this->assertEquals(1, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$user->include('articles');

		$this->assertTrue($user->includes('articles'));

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "articles"."user_id" IN (1)', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testIncludeOnResultSet(): void
	{
		$users = (new LazyHasManyUser)->where('id', '=', 1)->all();

		$this->assertFalse($users[0]->includes('articles'));

		$users->include('articles');

		$this->assertTrue($users[0]->includes('articles'));

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "articles"."user_id" IN (1)', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}
}
