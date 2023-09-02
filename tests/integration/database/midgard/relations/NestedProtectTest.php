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

class NestedProtectUser extends TestORM
{
	protected string $tableName = 'users';

	public function articles()
	{
		return $this->hasMany(NestedProtectArticle::class, 'user_id');
	}
}

class NestedPreProtectUser extends TestORM
{
	protected string $tableName = 'users';

	protected array $protected = ['id', 'articles.id', 'articles.comments.id'];

	public function articles()
	{
		return $this->hasMany(NestedProtectArticle::class, 'user_id');
	}
}

class NestedProtectArticle extends TestORM
{
	protected string $tableName = 'articles';

	public function comments()
	{
		return $this->hasMany(NestedProtectComment::class, 'article_id');
	}
}

class NestedProtectComment extends TestORM
{
	protected string $tableName = 'article_comments';
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
class NestedProtectTest extends ORMTestCase
{
	/**
	 *
	 */
	public function testNestedProtect(): void
	{
		$exposed = (new NestedProtectUser)
		->including(['articles', 'articles.comments'])
		->ascending('id')
		->first()
		->toArray();

		$this->assertTrue(array_key_exists('id', $exposed));
		$this->assertTrue(array_key_exists('id', $exposed['articles'][0]));
		$this->assertTrue(array_key_exists('id', $exposed['articles'][0]['comments'][0]));

		$protected = (new NestedProtectUser)
		->including(['articles', 'articles.comments'])
		->ascending('id')
		->first()
		->protect(['id', 'articles.id', 'articles.comments.id'])
		->toArray();

		$this->assertFalse(array_key_exists('id', $protected));
		$this->assertFalse(array_key_exists('id', $protected['articles'][0]));
		$this->assertFalse(array_key_exists('id', $protected['articles'][0]['comments'][0]));
	}

	/**
	 *
	 */
	public function testNestedPreProtect(): void
	{
		$protected = (new NestedPreProtectUser)
		->including(['articles', 'articles.comments'])
		->ascending('id')
		->first()
		->toArray();

		$this->assertFalse(array_key_exists('id', $protected));
		$this->assertFalse(array_key_exists('id', $protected['articles'][0]));
		$this->assertFalse(array_key_exists('id', $protected['articles'][0]['comments'][0]));

		$exposed = (new NestedPreProtectUser)
		->including(['articles', 'articles.comments'])
		->ascending('id')
		->first()
		->expose(['id', 'articles.id', 'articles.comments.id'])
		->toArray();

		$this->assertTrue(array_key_exists('id', $exposed));
		$this->assertTrue(array_key_exists('id', $exposed['articles'][0]));
		$this->assertTrue(array_key_exists('id', $exposed['articles'][0]['comments'][0]));
	}
}
