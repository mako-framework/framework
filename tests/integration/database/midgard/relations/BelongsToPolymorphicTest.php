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
	protected $tableName = 'profiles';
}

class BelongsToPolymorphicImage extends TestORM
{
	protected $tableName = 'images';

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

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "images" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "profiles" WHERE "profiles"."id" = \'1\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}
}
