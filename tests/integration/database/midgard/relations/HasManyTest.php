<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard\relations;

use Generator;
use mako\database\midgard\ResultSet;
use mako\tests\integration\ORMTestCase;
use mako\tests\integration\TestORM;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class HasManyUser extends TestORM
{
	protected $tableName = 'users';

	public function articles()
	{
		return $this->hasMany(HasManyArticle::class, 'user_id');
	}
}

class HasManyArticle extends TestORM
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
class HasManyTest extends ORMTestCase
{
	/**
	 *
	 */
	public function testBasicHasManyRelation(): void
	{
		$user = HasManyUser::get(1);

		$articles = $user->articles;

		$this->assertInstanceOf(ResultSet::class, $articles);

		$this->assertEquals(2, count($articles));

		foreach($articles as $article)
		{
			$this->assertInstanceOf(HasManyArticle::class, $article);

			$this->assertEquals($article->user_id, $user->id);
		}

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "articles"."user_id" = \'1\'', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testHasManyYield(): void
	{
		$user = HasManyUser::get(1);

		$generator = $user->articles()->yield();

		$this->assertInstanceOf(Generator::class, $generator);

		foreach($generator as $article)
		{
			$this->assertInstanceOf(HasManyArticle::class, $article);

			$this->assertEquals($article->user_id, $user->id);
		}

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "articles"."user_id" = \'1\'', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testLazyHasManyRelation(): void
	{
		$users = (new HasManyUser)->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf(ResultSet::class, $user->articles);

			foreach($user->articles as $article)
			{
				$this->assertInstanceOf(HasManyArticle::class, $article);

				$this->assertEquals($article->user_id, $user->id);
			}
		}

		$this->assertEquals(4, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "articles"."user_id" = \'1\'', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "articles"."user_id" = \'2\'', $this->connectionManager->getConnection('sqlite')->getLog()[2]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "articles"."user_id" = \'3\'', $this->connectionManager->getConnection('sqlite')->getLog()[3]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasManyRelation(): void
	{
		$users = (new HasManyUser)->including('articles')->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf(ResultSet::class, $user->articles);

			foreach($user->articles as $article)
			{
				$this->assertInstanceOf(HasManyArticle::class, $article);

				$this->assertEquals($article->user_id, $user->id);
			}
		}

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "articles"."user_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasManyRelationWithConstraint(): void
	{
		$users = (new HasManyUser)->including(['articles' => function($query): void
		{
			$query->where('title', '=', 'does not exist');
		}, ])->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf(ResultSet::class, $user->articles);

			$this->assertEquals(0, count($user->articles));
		}

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" ORDER BY "id" ASC', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "articles" WHERE "title" = \'does not exist\' AND "articles"."user_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testCreateRelated(): void
	{
		$user = HasManyUser::get(1);

		$article = new HasManyArticle();

		$article->created_at = '2014-04-30 15:02:10';

		$article->title = 'article 4';

		$article->body = 'article 4 body';

		$user->articles()->create($article);

		$this->assertEquals($article->user_id, $user->id);

		$article->delete();

		$this->assertEquals(3, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('INSERT INTO "articles" ("created_at", "title", "body", "user_id") VALUES (\'2014-04-30 15:02:10\', \'article 4\', \'article 4 body\', \'1\')', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testWithCountOf(): void
	{
		$user = (new HasManyUser)->withCountOf('articles')->get(1);

		$this->assertEquals(2, $user->articles_count);

		$this->assertEquals('SELECT *, (SELECT COUNT(*) FROM "articles" WHERE "articles"."user_id" = "users"."id") AS "articles_count" FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);
	}
}
