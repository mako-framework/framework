<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\cli\output\helpers\OrderedList;

/**
 * @group unit
 */
class OrderedListTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 *
	 */
	public function testBasicList()
	{
		$output = Mockery::mock('mako\cli\output\Output');

		$list = new OrderedList($output);

		$expected  = '';
		$expected .= '1. one' . PHP_EOL;
		$expected .= '2. two' . PHP_EOL;
		$expected .= '3. three' . PHP_EOL;

		$this->assertSame($expected, $list->render(['one', 'two', 'three']));
	}

	/**
	 *
	 */
	public function testNestedLists()
	{
		$output = Mockery::mock('mako\cli\output\Output');

		$list = new OrderedList($output);

		$expected  = '';
		$expected .= '1. one' . PHP_EOL;
		$expected .= '2. two' . PHP_EOL;
		$expected .= '3. three' . PHP_EOL;
		$expected .= '   1. one' . PHP_EOL;
		$expected .= '   2. two' . PHP_EOL;
		$expected .= '   3. three' . PHP_EOL;
		$expected .= '4. four' . PHP_EOL;

		$this->assertSame($expected, $list->render(['one', 'two', 'three', ['one', 'two', 'three'], 'four']));
	}

	/**
	 *
	 */
	public function testCustomMarker()
	{
		$output = Mockery::mock('mako\cli\output\Output');

		$list = new OrderedList($output);

		$expected  = '';
		$expected .= '[1] one' . PHP_EOL;
		$expected .= '[2] two' . PHP_EOL;
		$expected .= '[3] three' . PHP_EOL;

		$this->assertSame($expected, $list->render(['one', 'two', 'three'], '[%s]'));
	}

	/**
	 *
	 */
	public function testDraw()
	{
		$output = Mockery::mock('mako\cli\output\Output');

		$list = new OrderedList($output);

		$expected  = '';
		$expected .= '1. one' . PHP_EOL;
		$expected .= '2. two' . PHP_EOL;
		$expected .= '3. three' . PHP_EOL;

		$output->shouldReceive('write')->once()->with($expected, 1);

		$list->draw(['one', 'two', 'three']);
	}

	/**
	 *
	 */
	public function testDrawWithCustomMarker()
	{
		$output = Mockery::mock('mako\cli\output\Output');

		$list = new OrderedList($output);

		$expected  = '';
		$expected .= '[1] one' . PHP_EOL;
		$expected .= '[2] two' . PHP_EOL;
		$expected .= '[3] three' . PHP_EOL;

		$output->shouldReceive('write')->once()->with($expected, 1);

		$list->draw(['one', 'two', 'three'], '[%s]');
	}
}