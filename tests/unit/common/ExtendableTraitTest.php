<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\common;

use mako\common\traits\ExtendableTrait;
use mako\tests\TestCase;

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
class ExtendableTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testExtending(): void
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
	public function testException(): void
	{
		$collection = new Extended();

		$collection->nope();
	}
}
