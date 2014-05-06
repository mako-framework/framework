<?php

namespace mako\tests\integration\database\relations\midgard;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class HasManyUser extends \TestORM
{
	protected $tableName = 'users';

	public function articles()
	{
		return $this->hasMany('mako\tests\integration\database\relations\midgard\HasManyArticle', 'user_id');
	}
}

class HasManyArticle extends \TestORM
{
	protected $tableName = 'articles';

	public function user()
	{
		return $this->belongsTo('mako\tests\integration\database\relations\midgard\HasManyUser', 'user_id');
	}
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
			$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\HasManyArticle', $article);
		}
	}

	/**
	 * 
	 */

	public function testBasicBelongsToRelation()
	{
		$article = HasManyArticle::get(1);

		$user = $article->user;

		$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\HasManyUser', $user);

		$this->assertEquals('foo', $user->username);
	}

	/**
	 * 
	 */

	public function testLoopHasManyRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = HasManyUser::ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->articles);

			foreach($user->articles as $article)
			{
				$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\HasManyArticle', $article);
			}
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(4, $queryCountAfter - $queryCountBefore);
	}

	/**
	 * 
	 */

	public function testEagerLoopHasManyRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = HasManyUser::including('articles')->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->articles);

			foreach($user->articles as $article)
			{
				$this->assertInstanceOf('mako\tests\integration\database\relations\midgard\HasManyArticle', $article);
			}
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}
}