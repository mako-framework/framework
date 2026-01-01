<?php

/**
 * @copyright Frederic G. Østby
 * @license	http://www.makoframework.com/license
 */

namespace mako\tests\unit;

use mako\env\Type;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

use function mako\env;
use function mako\f;
use function mako\syringe\intersection;

#[Group('unit')]
#[RunTestsInSeparateProcesses]
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

		$this->assertSame(static::class . '([1,2,3])', f(static::class, [1, 2, 3]));

		$this->assertSame('foobar("a":"value","b":123,"c":true,"d":null,"e":[1,2,3])', f('foobar', a: 'value', b: 123, c: true, d: null, e: [1, 2, 3]));
	}

	/**
	 *
	 */
	public function testEnvWithMissingVariable(): void
	{
		$this->assertNull(env('MAKO_MISSING'));

		$this->assertSame('default', env('MAKO_MISSING', 'default'));
	}

	/**
	 *
	 */
	public function testEnvWithVariableFromEnvSuperglobal(): void
	{
		$_ENV['MAKO_FOOBAR1'] = 'hello';

		$this->assertSame('hello', env('MAKO_FOOBAR1'));
	}

	/**
	 *
	 */
	public function testEnvWithVariableFromGetenv(): void
	{
		putenv('MAKO_FOOBAR2=hello');

		$this->assertSame('hello', env('MAKO_FOOBAR2'));
	}

	/**
	 *
	 */
	public function testEnvWithBooleanValues(): void
	{
		$_ENV['MAKO_TRUE'] = 'true';
		$_ENV['MAKO_FALSE'] = 'false';

		$this->assertTrue(env('MAKO_TRUE', as: Type::BOOL));
		$this->assertFalse(env('MAKO_FALSE', as: Type::BOOL));

		$_ENV['MAKO_TRUE'] = '1';
		$_ENV['MAKO_FALSE'] = '0';

		$this->assertTrue(env('MAKO_TRUE', as: Type::BOOL));
		$this->assertFalse(env('MAKO_FALSE', as: Type::BOOL));
	}

	/**
	 *
	 */
	public function testEnvWithIntValues(): void
	{
		$_ENV['MAKO_VALID'] = '1234';
		$_ENV['MAKO_INVALID'] = 'foobar';

		$this->assertSame(1234, env('MAKO_VALID', as: Type::INT));
		$this->assertNull(env('MAKO_INVALID', as: Type::INT));
	}

	/**
	 *
	 */
	public function testEnvWithFloatValues(): void
	{
		$_ENV['MAKO_VALID'] = '1.2';
		$_ENV['MAKO_INVALID'] = 'foobar';

		$this->assertSame(1.2, env('MAKO_VALID', as: Type::FLOAT));
		$this->assertNull(env('MAKO_INVALID', as: Type::FLOAT));
	}

	/**
	 *
	 */
	public function testEnvWithJsonObjectValues(): void
	{
		$_ENV['MAKO_JSON'] = '{"foo": "bar"}';

		$value = env('MAKO_JSON', as: Type::JSON_AS_OBJECT);

		$this->assertIsObject($value);
		$this->assertSame('bar', $value->foo);

		$value = env('MAKO_NO_JSON', as: Type::JSON_AS_OBJECT);

		$this->assertNull($value);
	}

	/**
	 *
	 */
	public function testEnvWithJsonArrayValues(): void
	{
		$_ENV['MAKO_JSON'] = '{"foo": "bar"}';

		$value = env('MAKO_JSON', as: Type::JSON_AS_ARRAY);

		$this->assertIsArray($value);
		$this->assertSame('bar', $value['foo']);

		$value = env('MAKO_NO_JSON', as: Type::JSON_AS_ARRAY);

		$this->assertNull($value);
	}

	/**
	 *
	 */
	public function testIntersection(): void
	{
		$this->assertSame('foo&bar&baz', intersection('foo', 'bar', 'baz'));
	}
}
