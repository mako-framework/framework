<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard;

use mako\database\midgard\traits\TimestampedTrait;
use mako\tests\integration\ORMTestCase;
use mako\tests\integration\TestORM;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class TimestampedFoo extends TestORM
{
	use TimestampedTrait;

	protected $tableName = 'timestamped_foos';
}

class TimestampedBar extends TestORM
{
	use TimestampedTrait;

	protected $tableName = 'timestamped_bars';

	protected $touch = ['foo'];

	public function foo()
	{
		return $this->belongsTo(TimestampedFoo::class, 'timestamped_foo_id');
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
class TimestampedTest extends ORMTestCase
{
	 /**
	  *
	  */
	 public function testCreate(): void
	 {
	 	$timestamped = new TimestampedFoo;

	 	$timestamped->value = 'baz';

	 	$timestamped->save();

	 	$this->assertEquals($timestamped->created_at->getTimestamp(), $timestamped->updated_at->getTimestamp());
	 }

	 /**
	  *
	  */
	 public function testUpate(): void
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
	 public function testTouch(): void
	 {
	 	$timestamped = TimestampedFoo::get(3);

	 	$this->assertEquals($timestamped->created_at->getTimestamp(), $timestamped->updated_at->getTimestamp());

	 	$timestamped->touch();

	 	$this->assertNotEquals($timestamped->created_at->getTimestamp(), $timestamped->updated_at->getTimestamp());
	 }

	 /**
	  *
	  */
	 public function testTouchRelation(): void
	 {
	 	$timestamped = TimestampedBar::get(1);

	 	$this->assertNotEquals($timestamped->updated_at->getTimestamp(), $timestamped->foo()->first()->updated_at->getTimestamp());

	 	$timestamped->touch();

	 	$this->assertEquals($timestamped->updated_at->getTimestamp(), $timestamped->foo()->first()->updated_at->getTimestamp());
	 }
}
