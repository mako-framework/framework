<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\common\traits;

use BadMethodCallException;
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
		Extended::addMethod('foo', static function ()
		{
			return static::$foo;
		});

		Extended::addMethod('bar', function ()
		{
			return $this->bar;
		});

		$this->assertSame('foo', Extended::foo());

		$this->assertSame('bar', (new Extended)->bar());
	}

	/**
	 *
	 */
	public function testException(): void
	{
		$this->expectException(BadMethodCallException::class);

		$collection = new Extended();

		$collection->nope();
	}
}
