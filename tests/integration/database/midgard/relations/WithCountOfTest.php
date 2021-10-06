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

class WithCountOfUser extends TestOrm
{
	protected $tableName = 'users';

	public function articles()
	{
		return $this->hasMany(WithCountOfArticle::class, 'user_id')->descending('id');
	}

	public function profile()
	{
		return $this->hasOne(WithCountOfProfile::class, 'user_id')->descending('id');
	}
}

class WithCountOfArticle extends TestORM
{
	protected $tableName = 'articles';
}

class WithCountOfProfile extends TestORM
{
	protected $tableName = 'profiles';
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
class WithCountOfTest extends ORMTestCase
{
	/**
	 *
	 */
	public function testMultipleWithCountOf(): void
	{
		$user = WithCountOfUser::withCountOf(['articles', 'profile'])->get(1);

		$this->assertEquals(2, $user->articles_count);

		$this->assertEquals(1, $user->profile_count);

		$this->assertEquals('SELECT *, (SELECT COUNT(*) FROM "articles" WHERE "articles"."user_id" = "users"."id") AS "articles_count", (SELECT COUNT(*) FROM "profiles" WHERE "profiles"."user_id" = "users"."id") AS "profile_count" FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testMultipleWithCountOfWithCriteria(): void
	{
		$user = WithCountOfUser::withCountOf
		([
			'articles as no_articles_count' => function($query): void
			{
				$query->where('articles.id', '=', 0);
			},
			'profile AS no_profile_count' => function($query): void
			{
				$query->where('profiles.id', '=', 0);
			},
		])->select(['id'])->get(1);

		$this->assertEquals(0, $user->no_articles_count);

		$this->assertEquals(0, $user->no_profile_count);

		$this->assertEquals('SELECT "id", (SELECT COUNT(*) FROM "articles" WHERE "articles"."user_id" = "users"."id" AND "articles"."id" = 0) AS "no_articles_count", (SELECT COUNT(*) FROM "profiles" WHERE "profiles"."user_id" = "users"."id" AND "profiles"."id" = 0) AS "no_profile_count" FROM "users" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);
	}

	/**
	 *
	 */
	public function testMultipleWithCountOfWithAggregate(): void
	{
		$user = WithCountOfUser::withCountOf('articles')->count();

		$this->assertEquals('SELECT COUNT(*) FROM "users"', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);
	}
}
