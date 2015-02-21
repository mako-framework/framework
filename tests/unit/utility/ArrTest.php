<?php

namespace mako\tests\unit\utility;

use mako\utility\Arr;

/**
 * @group unit
 */

class ArrTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testSet()
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

	public function testHas()
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

	public function testGet()
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

	 public function testDelete()
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

	 public function testRandom()
	 {
	 	$arr = ['foo', 'bar', 'baz'];

	 	$this->assertTrue(in_array(Arr::random($arr), $arr));
	 }

	 /**
	  *
	  */

	 public function testIsAssoc()
	 {
	 	$this->assertTrue(Arr::isAssoc(['foo' => 0, 'bar' => 1]));

	 	$this->assertFalse(Arr::isAssoc([0 => 'foo', 1 => 'bar']));

	 	$this->assertFalse(Arr::isAssoc(['foo' => 0, 1 => 'bar']));
	 }

	 /**
	  *
	  */

	 public function testPluck()
	 {
	 	$arr = [['foo' => 'bar'], ['foo' => 'baz']];

	 	$this->assertEquals(['bar', 'baz'], Arr::pluck($arr, 'foo'));

	 	//

	 	$obj1 = new \StdClass;

	 	$obj1->foo = 'bar';

	 	$obj2 = new \StdClass;

	 	$obj2->foo = 'baz';

	 	$arr = [$obj1, $obj2];

	 	$this->assertEquals(['bar', 'baz'], Arr::pluck($arr, 'foo'));
	 }
}