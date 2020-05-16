<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard\relations;

use mako\database\midgard\ResultSet;
use mako\tests\integration\ORMTestCase;
use mako\tests\integration\TestORM;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class AutoEagerLoadingUser extends TestORM
{
	protected $tableName = 'users';

	protected $including = ['articles'];

	public function articles()
	{
		return $this->hasMany(AutoEagerLoadingArticle::class, 'user_id');
	}
}

class AutoEagerLoadingArticle extends TestORM
{
	protected $tableName = 'articles';
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
class AutoEagerLoadingTest extends ORMTestCase
{
	/**
	 *
	 */
	public function testAutoEagerLoading(): void
	{
		$users = AutoEagerLoadingUser::ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf(ResultSet::class, $user->articles);

			foreach($user->articles as $article)
			{
				$this->assertInstanceOf(AutoEagerLoadingArticle::class, $article);

				$this->assertEquals($article->user_id, $user->id);
			}
		}

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "articles"."user_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testDisableAutoEagerLoading(): void
	{
		$users = AutoEagerLoadingUser::excluding('articles')->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf(ResultSet::class, $user->articles);

			foreach($user->articles as $article)
			{
				$this->assertInstanceOf(AutoEagerLoadingArticle::class, $article);

				$this->assertEquals($article->user_id, $user->id);
			}
		}

		$this->assertEquals(4, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "articles"."user_id" = \'1\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "articles"."user_id" = \'2\'', $this->connectionManager->connection('sqlite')->getLog()[2]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "articles"."user_id" = \'3\'', $this->connectionManager->connection('sqlite')->getLog()[3]['query']);
	}
}
