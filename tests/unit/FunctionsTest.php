<?php

/**
 * @copyright Frederic G. Østby
 * @license	http://www.makoframework.com/license
 */

namespace mako\tests\unit;

use mako\tests\TestCase;

use function mako\f;

/**
 * @group unit
 */
class FunctionsTest extends TestCase
{
	/**
	 *
	 */
	public function testF(): void
	{
		$this->assertSame('foobar', f('foobar'));

		$this->assertSame('foobar(1,2,3)', f('foobar', 1, 2, 3));

		$this->assertSame('foobar("hello\"world")', f('foobar', 'hello"world'));

		$this->assertSame('foobar([1,2,3])', f('foobar', [1, 2, 3]));

		$this->assertSame('mako\tests\unit\FooBar([1,2,3])', f(FooBar::class, [1, 2, 3]));

		$this->assertSame('foobar("a":"value","b":123,"c":true,"d":null,"e":[1,2,3])', f('foobar', a: 'value', b: 123, c: true, d: null, e: [1,2,3]));
	}
}
