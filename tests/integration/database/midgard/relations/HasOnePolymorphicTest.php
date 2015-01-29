<?php

namespace mako\tests\integration\database\midgard\relations;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class HasOnePolymorphicProfile extends \TestORM
{
	protected $tableName = 'profiles';

	public function image()
	{
		return $this->hasOnePolymorphic('mako\tests\integration\database\midgard\relations\HasOnePolymorphicImage', 'imageable');
	}
}

class HasOnePolymorphicImage extends \TestORM
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

class HasOnePolymorphicTest extends \ORMTestCase
{
	/**
	 *
	 */

	public function testBasicHasOneRelation()
	{
		$profile = HasOnePolymorphicProfile::get(1);

		$image = $profile->image;

		$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOnePolymorphicImage', $image);

		$this->assertEquals($profile->getClass(), $image->imageable_type);

		$this->assertEquals($profile->id, $image->imageable_id);
	}

	/**
	 *
	 */

	public function testLazyHasOneRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$profiles = HasOnePolymorphicProfile::ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOnePolymorphicImage', $profile->image);

			$this->assertEquals($profile->getClass(), $profile->image->imageable_type);

			$this->assertEquals($profile->id, $profile->image->imageable_id);
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(4, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testEagerHasOneRelation()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$profiles = HasOnePolymorphicProfile::including('image')->ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertInstanceOf('mako\tests\integration\database\midgard\relations\HasOnePolymorphicImage', $profile->image);

			$this->assertEquals($profile->getClass(), $profile->image->imageable_type);

			$this->assertEquals($profile->id, $profile->image->imageable_id);
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testEagerHasOneRelationWithConstraint()
	{
		$queryCountBefore = count($this->connectionManager->connection('sqlite')->getLog());

		$profiles = HasOnePolymorphicProfile::including(['image' => function($query)
		{
			$query->where('image', '=', 'does not exist');
		}])->ascending('id')->all();

		foreach($profiles as $profile)
		{
			$this->assertFalse($profile->image);
		}

		$queryCountAfter = count($this->connectionManager->connection('sqlite')->getLog());

		$this->assertEquals(2, $queryCountAfter - $queryCountBefore);
	}

	/**
	 *
	 */

	public function testCreateRelated()
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
	}
}