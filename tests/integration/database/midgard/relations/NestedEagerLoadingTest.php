<?php

namespace mako\tests\integration\database\midgard\relations;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class NestedEagerLoadingUser extends \TestORM
{
	protected $tableName = 'users';

	public function articles()
	{
		return $this->hasMany('mako\tests\integration\database\midgard\relations\NestedEagerLoadingArticle', 'user_id');
	}
}

class NestedEagerLoadingArticle extends \TestORM
{
	protected $tableName = 'articles';

	public function comments()
	{
		return $this->hasMany('mako\tests\integration\database\midgard\relations\NestedEagerLoadingComment', 'article_id');
	}
}

class NestedEagerLoadingComment extends \TestORM
{
	protected $tableName = 'article_comments';
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

class NestedEagerLoadingTest extends \ORMTestCase
{
	/**
	 *
	 */

	public function testNestedEagerLoading()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = NestedEagerLoadingUser::including(['articles', 'articles.comments'])->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->articles);

			foreach($user->articles as $article)
			{
				$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\NestedEagerLoadingArticle', $article);

				$this->assertEquals($article->user_id, $user->id);

				$this->assertInstanceOf('mako\database\midgard\ResultSet', $article->comments);

				foreach($article->comments as $comment)
				{
					$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\NestedEagerLoadingComment', $comment);

					$this->assertEquals($comment->article_id, $article->id);
				}
			}
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(3, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testNestedEagerLoadingWithConstraints()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = NestedEagerLoadingUser::including(['articles', 'articles.comments' => function($query)
		{
			$query->where('comment', '=', 'does not exist');
		}])->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->articles);

			foreach($user->articles as $article)
			{
				$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\NestedEagerLoadingArticle', $article);

				$this->assertEquals($article->user_id, $user->id);

				$this->assertInstanceOf('mako\database\midgard\ResultSet', $article->comments);

				$this->assertEquals(0, count($article->comments));
			}
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(3, $queryCountAfter - $queryCountBefore);
	}
}