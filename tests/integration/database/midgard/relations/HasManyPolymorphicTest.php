<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

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

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "articles" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "commentable_id" = \'1\' AND "commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testHasManyYield()
	{
		$article = HasManyPolymorphicArticle::get(1);

		$generator = $article->comments()->yield();

		$this->assertInstanceOf('Generator', $generator);

		foreach($generator as $comment)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasManyPolymorphicComment', $comment);

			$this->assertEquals($comment->commentable_type, $article->getClass());

			$this->assertEquals($comment->commentable_id, $article->id);
		}

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "articles" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "commentable_id" = \'1\' AND "commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testLazyHasManyRelation()
	{
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

		$this->assertEquals(4, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "articles" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "commentable_id" = \'1\' AND "commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "commentable_id" = \'2\' AND "commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\'', $this->connectionManager->connection('sqlite')->getLog()[2]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "commentable_id" = \'3\' AND "commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\'', $this->connectionManager->connection('sqlite')->getLog()[3]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasManyRelation()
	{
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

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "articles" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\' AND "commentable_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasManyRelationWithConstraint()
	{
		$articles = HasManyPolymorphicArticle::including(['comments' => function($query)
		{
			$query->where('comment', '=', 'does not exist');
		}, ])->ascending('id')->all();

		foreach($articles as $article)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $article->comments);

			$this->assertEquals(0, count($article->comments));
		}

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "articles" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\' AND "comment" = \'does not exist\' AND "commentable_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
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

		$this->assertEquals(3, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "articles" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('INSERT INTO "polymorphic_comments" ("created_at", "comment", "user_id", "commentable_type", "commentable_id") VALUES (\'2014-04-30 15:02:10\', \'this is a comment\', 1, \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\', \'1\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}
}
