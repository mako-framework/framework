<?php

/**
 * @copyright Frederic G. Østby
 * @license	http://www.makoframework.com/license
 */

namespace mako\tests\unit;

use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

use function mako\env;
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

		$this->assertSame('foobar("a":"value","b":123,"c":true,"d":null,"e":[1,2,3])', f('foobar', a: 'value', b: 123, c: true, d: null, e: [1, 2, 3]));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testEnvWithMissingVariable(): void
	{
		$this->assertNull(env('MAKO_MISSING'));

		$this->assertSame('default', env('MAKO_MISSING', 'default'));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testEnvWithVariableFromEnvSuperglobal(): void
	{
		$_ENV['MAKO_FOOBAR'] = 'hello';

		$this->assertSame('hello', env('MAKO_FOOBAR'));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testEnvWithVariableFromGetenv(): void
	{
		putenv('MAKO_FOOBAR=hello');

		$this->assertSame('hello', env('MAKO_FOOBAR'));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testEnvWithBooleanValues(): void
	{
		$_ENV['MAKO_TRUE'] = 'true';
		$_ENV['MAKO_FALSE'] = 'false';

		$this->assertTrue(env('MAKO_TRUE', isBool: true));
		$this->assertFalse(env('MAKO_FALSE', isBool: true));
	}
}
