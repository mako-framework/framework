<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\common\traits;

use mako\common\traits\SuggestionTrait;
use mako\tests\TestCase;

/**
 * @group unit
 */
class SuggestionTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testNoSuggestionFound(): void
	{
		$class = new class {
			use SuggestionTrait;

			public function test()
			{
				return $this->suggest('foo', ['bar', 'baz']);
			}
		};

		$this->assertNull($class->test());
	}

	/**
	 *
	 */
	public function testSuggestionFound(): void
	{
		$class = new class {
			use SuggestionTrait;

			public function test()
			{
				return $this->suggest('sevrer', ['server']);
			}
		};

		$this->assertEquals('server', $class->test());
	}
}
