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

class HasManyPolymorphicArticle extends TestORM
{
	protected string $tableName = 'articles';

	public function comments()
	{
		return $this->hasManyPolymorphic(HasManyPolymorphicComment::class, 'commentable');
	}
}

class HasManyPolymorphicComment extends TestORM
{
	protected string $tableName = 'polymorphic_comments';
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
class HasManyPolymorphicTest extends ORMTestCase
{
	/**
	 *
	 */
	public function testBasicHasManyRelation(): void
	{
		$article = HasManyPolymorphicArticle::get(1);

		$comments = $article->comments;

		$this->assertInstanceOf(ResultSet::class, $comments);

		$this->assertEquals(2, count($comments));

		foreach ($comments as $comment) {
			$this->assertInstanceOf(HasManyPolymorphicComment::class, $comment);

			$this->assertEquals($comment->commentable_type, $article->getClass());

			$this->assertEquals($comment->commentable_id, $article->id);
		}

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "articles" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "polymorphic_comments"."commentable_id" = \'1\' AND "polymorphic_comments"."commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\'', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testHasManyYield(): void
	{
		$article = HasManyPolymorphicArticle::get(1);

		$generator = $article->comments()->yield();

		$this->assertInstanceOf(Generator::class, $generator);

		foreach ($generator as $comment) {
			$this->assertInstanceOf(HasManyPolymorphicComment::class, $comment);

			$this->assertEquals($comment->commentable_type, $article->getClass());

			$this->assertEquals($comment->commentable_id, $article->id);
		}

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "articles" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "polymorphic_comments"."commentable_id" = \'1\' AND "polymorphic_comments"."commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\'', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testLazyHasManyRelation(): void
	{
		$articles = (new HasManyPolymorphicArticle)->ascending('id')->all();

		foreach ($articles as $article) {
			$this->assertInstanceOf(ResultSet::class, $article->comments);

			foreach ($article->comments as $comment) {
				$this->assertInstanceOf(HasManyPolymorphicComment::class, $comment);

				$this->assertEquals($comment->commentable_type, $article->getClass());

				$this->assertEquals($comment->commentable_id, $article->id);
			}
		}

		$this->assertEquals(4, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "articles" ORDER BY "id" ASC', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "polymorphic_comments"."commentable_id" = \'1\' AND "polymorphic_comments"."commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\'', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "polymorphic_comments"."commentable_id" = \'2\' AND "polymorphic_comments"."commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\'', $this->connectionManager->getConnection('sqlite')->getLog()[2]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "polymorphic_comments"."commentable_id" = \'3\' AND "polymorphic_comments"."commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\'', $this->connectionManager->getConnection('sqlite')->getLog()[3]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasManyRelation(): void
	{
		$articles = (new HasManyPolymorphicArticle)->including('comments')->ascending('id')->all();

		foreach ($articles as $article) {
			$this->assertInstanceOf(ResultSet::class, $article->comments);

			foreach ($article->comments as $comment) {
				$this->assertInstanceOf(HasManyPolymorphicComment::class, $comment);

				$this->assertEquals($comment->commentable_type, $article->getClass());

				$this->assertEquals($comment->commentable_id, $article->id);
			}
		}

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "articles" ORDER BY "id" ASC', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "polymorphic_comments"."commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\' AND "polymorphic_comments"."commentable_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasManyRelationWithConstraint(): void
	{
		$articles = (new HasManyPolymorphicArticle)->including(['comments' => function ($query): void {
			$query->where('comment', '=', 'does not exist');
		}, ])->ascending('id')->all();

		foreach ($articles as $article) {
			$this->assertInstanceOf(ResultSet::class, $article->comments);

			$this->assertEquals(0, count($article->comments));
		}

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "articles" ORDER BY "id" ASC', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "polymorphic_comments" WHERE "polymorphic_comments"."commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\' AND "comment" = \'does not exist\' AND "polymorphic_comments"."commentable_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testCreateRelated(): void
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

		$this->assertEquals(3, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "articles" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('INSERT INTO "polymorphic_comments" ("created_at", "comment", "user_id", "commentable_type", "commentable_id") VALUES (\'2014-04-30 15:02:10\', \'this is a comment\', 1, \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\', \'1\')', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testWithCountOf(): void
	{
		$article = (new HasManyPolymorphicArticle)->withCountOf('comments')->get(1);

		$this->assertEquals(2, $article->comments_count);

		$this->assertEquals('SELECT *, (SELECT COUNT(*) FROM "polymorphic_comments" WHERE "polymorphic_comments"."commentable_type" = \'\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle\' AND "polymorphic_comments"."commentable_id" = "articles"."id") AS "comments_count" FROM "articles" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);
	}
}
