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

class HasOnePolymorphicProfile extends TestORM
{
	protected $tableName = 'profiles';

	public function image()
	{
		return $this->hasOnePolymorphic('mako\tests\integration\database\midgard\relations\HasOnePolymorphicImage', 'imageable');
	}
}

class HasOnePolymorphicImage extends TestORM
{
	protected $tableName = 'images';
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
class HasOnePolymorphicTest extends ORMTestCase
{
	/**
	 *
	 */
	public function testBasicHasOneRelation(): void
	{
		$profile = HasOnePolymorphicProfile::get(1);

		$image = $profile->image;

		$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOnePolymorphicImage', $image);

		$this->assertEquals($profile->getClass(), $image->imageable_type);

		$this->assertEquals($profile->id, $image->imageable_id);

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "profiles" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "images" WHERE "images"."imageable_id" = \'1\' AND "images"."imageable_type" = \'\mako\tests\integration\database\midgard\relations\HasOnePolymorphicProfile\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testHasOneYield(): void
	{
		$profile = HasOnePolymorphicProfile::get(1);

		$generator = $profile->image()->yield();

		$this->assertInstanceOf('Generator', $generator);

		$count = 0;

		foreach($generator as $image)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOnePolymorphicImage', $image);

			$this->assertEquals($profile->getClass(), $image->imageable_type);

			$this->assertEquals($profile->id, $image->imageable_id);

			$count++;
		}

		$this->assertEquals(1, $count);

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "profiles" WHERE "id" = 1 LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "images" WHERE "images"."imageable_id" = \'1\' AND "images"."imageable_type" = \'\mako\tests\integration\database\midgard\relations\HasOnePolymorphicProfile\'', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testLazyHasOneRelation(): void
	{
		$profiles = HasOnePolymorphicProfile::ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOnePolymorphicImage', $profile->image);

			$this->assertEquals($profile->getClass(), $profile->image->imageable_type);

			$this->assertEquals($profile->id, $profile->image->imageable_id);
		}

		$this->assertEquals(4, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "profiles" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "images" WHERE "images"."imageable_id" = \'1\' AND "images"."imageable_type" = \'\mako\tests\integration\database\midgard\relations\HasOnePolymorphicProfile\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);

		$this->assertEquals('SELECT * FROM "images" WHERE "images"."imageable_id" = \'2\' AND "images"."imageable_type" = \'\mako\tests\integration\database\midgard\relations\HasOnePolymorphicProfile\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[2]['query']);

		$this->assertEquals('SELECT * FROM "images" WHERE "images"."imageable_id" = \'3\' AND "images"."imageable_type" = \'\mako\tests\integration\database\midgard\relations\HasOnePolymorphicProfile\' LIMIT 1', $this->connectionManager->connection('sqlite')->getLog()[3]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasOneRelation(): void
	{
		$profiles = HasOnePolymorphicProfile::including('image')->ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOnePolymorphicImage', $profile->image);

			$this->assertEquals($profile->getClass(), $profile->image->imageable_type);

			$this->assertEquals($profile->id, $profile->image->imageable_id);
		}

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "profiles" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "images" WHERE "images"."imageable_type" = \'\mako\tests\integration\database\midgard\relations\HasOnePolymorphicProfile\' AND "images"."imageable_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testEagerHasOneRelationWithConstraint(): void
	{
		$profiles = HasOnePolymorphicProfile::including(['image' => function($query): void
		{
			$query->where('image', '=', 'does not exist');
		}, ])->ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertFalse($profile->image);
		}

		$this->assertEquals(2, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('SELECT * FROM "profiles" ORDER BY "id" ASC', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('SELECT * FROM "images" WHERE "images"."imageable_type" = \'\mako\tests\integration\database\midgard\relations\HasOnePolymorphicProfile\' AND "image" = \'does not exist\' AND "images"."imageable_id" IN (\'1\', \'2\', \'3\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}

	/**
	 *
	 */
	public function testCreateRelated(): void
	{
		$profile = new HasOnePolymorphicProfile;

		$profile->user_id = 4;

		$profile->interests = 'games';

		$profile->save();

		$image = new HasOnePolymorphicImage;

		$image->image = 'bax.png';

		$profile->image()->create($image);

		$this->assertEquals($profile->getClass(), $image->imageable_type);

		$this->assertEquals($profile->id, $image->imageable_id);

		$image->delete();

		$profile->delete();

		$this->assertEquals(4, count($this->connectionManager->connection('sqlite')->getLog()));

		$this->assertEquals('INSERT INTO "profiles" ("user_id", "interests") VALUES (4, \'games\')', $this->connectionManager->connection('sqlite')->getLog()[0]['query']);

		$this->assertEquals('INSERT INTO "images" ("image", "imageable_type", "imageable_id") VALUES (\'bax.png\', \'\mako\tests\integration\database\midgard\relations\HasOnePolymorphicProfile\', \'4\')', $this->connectionManager->connection('sqlite')->getLog()[1]['query']);
	}
}
