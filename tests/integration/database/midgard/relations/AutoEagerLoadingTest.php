<?php

namespace mako\tests\integration\database\midgard\relations;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class AutoEagerLoadingUser extends \TestORM
{
	protected $tableName = 'users';

	protected $including = ['articles'];

	public function articles()
	{
		return $this->hasMany('mako\tests\integration\database\midgard\relations\AutoEagerLoadingArticle');
	}
}

class AutoEagerLoadingArticle extends \TestORM
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

class AutoEagerLoadingTest extends \ORMTestCase
{
	/**
	 *
	 */

	public function testAutoEagerLoading()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = AutoEagerLoadingUser::ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->articles);

			foreach($user->articles as $article)
			{
				$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\AutoEagerLoadingArticle', $article);

				$this->assertEquals($article->user_id, $user->id);
			}
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testDisableAutoEagerLoading()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$users = AutoEagerLoadingUser::excluding('articles')->ascending('id')->all();

		foreach($users as $user)
		{
			$this->assertInstanceOf('mako\database\midgard\ResultSet', $user->articles);

			foreach($user->articles as $article)
			{
				$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\AutoEagerLoadingArticle', $article);

				$this->assertEquals($article->user_id, $user->id);
			}
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(4, $queryCountAfter - $queryCountBefore);
	}
}