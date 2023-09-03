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

class BelongsToPolymorphicProfile extends TestORM
{
	protected string $tableName = 'profiles';
}

class BelongsToPolymorphicImage extends TestORM
{
	protected string $tableName = 'images';

	public function profile()
	{
		return $this->belongsToPolymorphic(BelongsToPolymorphicProfile::class, 'imageable');
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
class BelongsToPolymorphicTest extends ORMTestCase
{
	/**
	 *
	 */
	public function testBasicBelongsToPolymorphicRelation(): void
	{
		$image = BelongsToPolymorphicImage::get(1);

		$profile = $image->profile;

		$this->assertInstanceOf(BelongsToPolymorphicProfile::class, $profile);

		$this->assertEquals($image->imageable_id, $profile->id);

		$this->assertEquals(2, count($this->connectionManager->getConnection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "images" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "profiles"."id" = \'1\' LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testWithCountOf(): void
	{
		$image = (new BelongsToPolymorphicImage)->withCountOf('profile')->get(1);

		$this->assertEquals(1, $image->profile_count);

		$this->assertEquals('SELECT *, (SELECT COUNT(*) FROM "profiles" WHERE "profiles"."id" = "images"."imageable_id") AS "profile_count" FROM "images" WHERE "id" = 1 LIMIT 1', $this->connectionManager->getConnection('sqlite')->getLog()[0]['query']);
	}
}
