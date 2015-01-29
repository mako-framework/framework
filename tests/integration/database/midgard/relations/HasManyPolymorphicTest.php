<?php

namespace mako\tests\integration\database\midgard\relations;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class HasManyPolymorphicArticle extends \TestORM
{
	protected $tableName = 'articles';

	public function comments()
	{
		return $this->hasManyPolymorphic('mako\tests\integration\database\midgard\relations\HasManyPolymorphicComment', 'commentable');
	}
}

class HasManyPolymorphicComment extends \TestORM
{
	protected $tableName = 'polymorphic_comments';
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

class HasManyPolymorphicTest extends \ORMTestCase
{
	/**
	 *
	 */

	public function testBasicHasManyRelation()
	{
		$article = HasManyPolymorphicArticle::get(1);

		$comments = $article->comments;

		$this->assertInstanceOf('mako\database\midgard\ResultSet', $comments);

		$this->assertEquals(2, count($comments));

		foreach($comments as $comment)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasManyPolymorphicComment', $comment);

			$this->assertEquals($comment->commentable_type, $article->getClass());

			$this->assertEquals($comment->commentable_id, $article->id);
		}
	}

	/**
	 *
	 */

	public function testLazyHasManyRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$articles = HasManyPolymorphicArticle::ascending('id')->all();

		foreach($articles as $article)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $article->comments);

			foreach($article->comments as $comment)
			{
				$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasManyPolymorphicComment', $comment);

				$this->assertEquals($comment->commentable_type, $article->getClass());

				$this->assertEquals($comment->commentable_id, $article->id);
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

		$articles = HasManyPolymorphicArticle::including('comments')->ascending('id')->all();

		foreach($articles as $article)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $article->comments);

			foreach($article->comments as $comment)
			{
				$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasManyPolymorphicComment', $comment);

				$this->assertEquals($comment->commentable_type, $article->getClass());

				$this->assertEquals($comment->commentable_id, $article->id);
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

		$articles = HasManyPolymorphicArticle::including(['comments' => function($query)
		{
			$query->where('comment', '=', 'does not exist');
		}])->ascending('id')->all();

		foreach($articles as $article)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $article->comments);

			$this->assertEquals(0, count($article->comments));
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testCreateRelated()
	{
		$article = HasManyPolymorphicArticle::get(1);

		$comment = new HasManyPolymorphicComment();

		$comment->created_at = '2014-04-30 15:02:10';

		$comment->comment = 'this is a comment';

		$comment->user_id = 1;

		$article->comments()->create($comment);

		$this->assertEquals($comment->commentable_type, $article->getClass());

		$this->assertEquals($comment->commentable_id, $article->id);

		$comment->delete();
	}
}