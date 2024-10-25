<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\midgard\traits;

use mako\database\midgard\traits\CamelCasedDataExportTrait;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Test
{
	public function toArray(): array
	{
		return ['foo_bar' => 1, 'bar_foo' => 2];
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('unit')]
class CamelCasedDataExportTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testToArray(): void
	{
		$class = new class extends Test {
			use CamelCasedDataExportTrait;
		};

		$this->assertSame(['fooBar', 'barFoo'], array_keys($class->toArray()));
	}
}
