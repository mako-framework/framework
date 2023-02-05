<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility;

use mako\tests\TestCase;
use mako\utility\Arr;
use mako\utility\exceptions\ArrException;
use stdClass;

/**
 * @group unit
 */
class ArrTest extends TestCase
{
	/**
	 *
	 */
	public function testSet(): void
	{
		$arr = [];

		Arr::set($arr, 'foo', '123');

		Arr::set($arr, 'bar.baz', '456');

		Arr::set($arr, 'bar.bax.0', '789');

		$this->assertEquals(['foo' => '123', 'bar' => ['baz' => '456', 'bax' => ['789']]], $arr);
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$arr = ['foo' => '123', 'bar' => ['baz' => '456', 'bax' => ['789']]];

		$this->assertTrue(Arr::has($arr, 'foo'));

		$this->assertTrue(Arr::has($arr, 'bar.baz'));

		$this->assertTrue(Arr::has($arr, 'bar.bax.0'));

		$this->assertFalse(Arr::has($arr, 'bar.bax.1'));
	}

	/**
	 *
	 */
	public function testHasNullValue(): void
	{
		$arr = ['foo' => null];

		$this->assertTrue(Arr::has($arr, 'foo'));
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$arr = ['foo' => '123', 'bar' => ['baz' => '456', 'bax' => ['789']]];

		$this->assertEquals('123', Arr::get($arr, 'foo'));

		$this->assertEquals('456', Arr::get($arr, 'bar.baz'));

		$this->assertEquals('789', Arr::get($arr, 'bar.bax.0'));

		$this->assertEquals('abc', Arr::get($arr, 'bar.bax.1', 'abc'));
	}

	/**
	 *
	 */
	public function testGetNullValue(): void
	{
		$arr = ['foo' => null];

		$this->assertNull(Arr::get($arr, 'foo', 'bar'));
	}

	/**
	 *
	 */
	public function testDelete(): void
	{
		$arr = ['foo' => '123', 'bar' => ['baz' => '456', 'bax' => ['789']]];

		$this->assertTrue(Arr::delete($arr, 'foo'));

		$this->assertTrue(Arr::delete($arr, 'bar.baz'));

		$this->assertTrue(Arr::delete($arr, 'bar.bax.0'));

		$this->assertFalse(Arr::delete($arr, 'nope.nope'));

		$this->assertEquals(['bar' => ['bax' => []]], $arr);
	}

	/**
	 *
	 */
	public function testRandom(): void
	{
		$arr = ['foo', 'bar', 'baz'];

		$this->assertTrue(in_array(Arr::random($arr), $arr));
	}

	/**
	 *
	 */
	public function testIsAssoc(): void
	{
		$this->assertTrue(Arr::isAssoc(['foo' => 0, 'bar' => 1]));

		$this->assertFalse(Arr::isAssoc([0 => 'foo', 1 => 'bar']));

		$this->assertFalse(Arr::isAssoc(['foo' => 0, 1 => 'bar']));
	}

	/**
	 *
	 */
	public function testPluck(): void
	{
		$arr = [['foo' => 'bar'], ['foo' => 'baz']];

		$this->assertEquals(['bar', 'baz'], Arr::pluck($arr, 'foo'));

		//

		$obj1 = new stdClass;

		$obj1->foo = 'bar';

		$obj2 = new stdClass;

		$obj2->foo = 'baz';

		$arr = [$obj1, $obj2];

		$this->assertEquals(['bar', 'baz'], Arr::pluck($arr, 'foo'));
	}

	/**
	 *
	 */
	public function testExpandKey(): void
	{
		$arr = ['foo' => ['bar' => [1, 2], 'baz' => [1, 2]]];

		$this->assertSame(['foo'], Arr::expandKey($arr, '*'));

		$this->assertSame(['foo.bar', 'foo.baz'], Arr::expandKey($arr, '*.*'));

		$this->assertSame(['foo.bar.0', 'foo.bar.1', 'foo.baz.0', 'foo.baz.1'], Arr::expandKey($arr, '*.*.*'));

		$this->assertSame(['foo.bar'], Arr::expandKey($arr, '*.bar'));

		$this->assertSame(['foo.bar.0', 'foo.bar.1'], Arr::expandKey($arr, '*.bar.*'));

		$this->assertSame(['foo.bar', 'foo.baz'], Arr::expandKey($arr, 'foo.*'));

		$this->assertSame(['foo.baz.0', 'foo.baz.1'], Arr::expandKey($arr, 'foo.baz.*'));

		$this->assertSame(['foo.bar.1', 'foo.baz.1'], Arr::expandKey($arr, 'foo.*.1'));

		$this->assertSame([], Arr::expandKey($arr, 'bax.*'));

		$this->assertSame([], Arr::expandKey($arr, 'foo.bax.*'));
	}

	/**
	 *
	 */
	public function testAssociativeArrayToObject(): void
	{
		$converted = Arr::toObject(['foo' => 1, 'bar' => 2]);

		$this->assertIsObject($converted);

		$this->assertSame(1, $converted->foo);

		$this->assertSame(2, $converted->bar);
	}

	/**
	 *
	 */
	public function testNumericArrayToObject(): void
	{
		$converted = Arr::toObject([1, 2]);

		$this->assertIsArray($converted);

		$this->assertSame(1, $converted[0]);

		$this->assertSame(2, $converted[1]);
	}

	/**
	 *
	 */
	public function testNestdArrayToObject(): void
	{
		$converted = Arr::toObject(['foo' => 1, 'bar' => 2, 'baz' => [3]]);

		$this->assertIsObject($converted);

		$this->assertSame(1, $converted->foo);

		$this->assertSame(2, $converted->bar);

		$this->assertIsArray($converted->baz);

		$this->assertSame(3, $converted->baz[0]);
	}

	/**
	 *
	 */
	public function testMixedArrayToObject(): void
	{
		$this->expectException(ArrException::class);

		$this->expectExceptionMessage('Unable to convert an array containing a mix of integer and string keys to an object.');

		$converted = Arr::toObject([1, 'foo' => 2]);
	}
}
