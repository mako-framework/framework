<?php

namespace mako\tests\unit\common;

use mako\common\ExtendableTrait;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Extended
{
	use ExtendableTrait;

	protected static $foo = 'foo';

	protected $bar = 'bar';
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class ExtendableTraitTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testExtending()
	{
		Extended::extend('foo', function()
		{
			return static::$foo;
		});

		Extended::extend('bar', function()
		{
			return $this->bar;
		});

		$this->assertSame('foo', Extended::foo());

		$this->assertSame('bar', (new Extended)->bar());
	}

	/**
	 * @expectedException \BadMethodCallException
	 */

	public function testException()
	{
		$collection = new Extended();

		$collection->nope();
	}
}
