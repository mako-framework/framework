<?php

namespace mako\tests\integration\database\midgard\relations;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class HasManyUser extends \TestORM
{
	protected $tableName = 'users';

	public function articles()
	{
		return $this->hasMany('mako\tests\integration\database\midgard\relations\HasManyArticle', 'user_id');
	}
}

class HasManyArticle extends \TestORM
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

class HasManyTest extends \ORMTestCase
{
	/**
	 *
	 */

	public function testBasicHasManyRelation()
	{
		$user = HasManyUser::get(1);

		$articles = $user->articles;

		$this->assertInstanceOf('mako\database\midgard\ResultSet', $articles);

		$this->assertEquals(2, count($articles));

		foreach($articles as $article)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasManyArticle', $article);

			$this->assertEquals($article->user_id, $user->id);
		}
	}

	/**
	 *
	 */

	public function testLazyHasManyRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = HasManyUser::ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->articles);

			foreach($user->articles as $article)
			{
				$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasManyArticle', $article);

				$this->assertEquals($article->user_id, $user->id);
			}
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(4, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testEagerHasManyRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = HasManyUser::including('articles')->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->articles);

			foreach($user->articles as $article)
			{
				$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasManyArticle', $article);

				$this->assertEquals($article->user_id, $user->id);
			}
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testEagerHasManyRelationWithConstraint()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = HasManyUser::including(['articles' => function($query)
		{
			$query->where('title', '=', 'does not exist');
		}])->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->articles);

			$this->assertEquals(0, count($user->articles));
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testCreateRelated()
	{
		$user = HasManyUser::get(1);

		$article = new HasManyArticle();

		$article->created_at = '014-04-30 15:02:10';

		$article->title = 'article 4';

		$article->body = 'article 4 body';

		$user->articles()->create($article);

		$this->assertEquals($article->user_id, $user->id);

		$article->delete();
	}
}