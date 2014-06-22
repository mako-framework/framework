<?php

namespace mako\tests\integration\database\midgard;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class TimestampedFoo extends \TestORM
{
	use \mako\database\midgard\traits\TimestampedTrait;

	protected $tableName = 'timestamped_foos';
}

class TimestampedBar extends \TestORM
{
	use \mako\database\midgard\traits\TimestampedTrait;

	protected $tableName = 'timestamped_bars';

	protected $touch = ['foo'];

	public function foo()
	{
		return $this->belongsTo('mako\tests\integration\database\midgard\TimestampedFoo', 'timestamped_foo_id');
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

class TimestampedTest extends \ORMTestCase
{
	/**
	 *
	 */

	 public function testCreate()
	 {
	 	$timestamped = new TimestampedFoo;

	 	$timestamped->value = 'baz';

	 	$timestamped->save();

	 	$this->assertEquals($timestamped->created_at->getTimestamp(), $timestamped->updated_at->getTimestamp());
	 }

	 /**
	  *
	  */

	 public function testUpate()
	 {
	 	$timestamped = TimestampedFoo::get(2);

	 	$this->assertEquals($timestamped->created_at->getTimestamp(), $timestamped->updated_at->getTimestamp());

	 	// Save without making changes

	 	$timestamped->save();

	 	$this->assertEquals($timestamped->created_at->getTimestamp(), $timestamped->updated_at->getTimestamp());

	 	// Save after making a change

	 	$timestamped->value = 'bax';

	 	$timestamped->save();

	 	$this->assertNotEquals($timestamped->created_at->getTimestamp(), $timestamped->updated_at->getTimestamp());
	 }

	 /**
	  *
	  */

	 public function testTouch()
	 {
	 	$timestamped = TimestampedFoo::get(3);

	 	$this->assertEquals($timestamped->created_at->getTimestamp(), $timestamped->updated_at->getTimestamp());

	 	$timestamped->touch();

	 	$this->assertNotEquals($timestamped->created_at->getTimestamp(), $timestamped->updated_at->getTimestamp());
	 }

	 /**
	  *
	  */

	 public function testTouchRelation()
	 {
	 	$timestamped = TimestampedBar::get(1);

	 	$this->assertNotEquals($timestamped->updated_at->getTimestamp(), $timestamped->foo()->first()->updated_at->getTimestamp());

	 	$timestamped->touch();

	 	$this->assertEquals($timestamped->updated_at->getTimestamp(), $timestamped->foo()->first()->updated_at->getTimestamp());
	 }
}