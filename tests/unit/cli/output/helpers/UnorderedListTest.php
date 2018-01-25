<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use Mockery;

use mako\cli\output\helpers\UnorderedList;
use mako\tests\TestCase;

/**
 * @group unit
 */
class UnorderedListTest extends TestCase
{
	/**
	 *
	 */
	public function testBasicList()
	{
		$output = Mockery::mock('mako\cli\output\Output');

		$list = new UnorderedList($output);

		$expected  = '';
		$expected .= '* one' . PHP_EOL;
		$expected .= '* two' . PHP_EOL;
		$expected .= '* three' . PHP_EOL;

		$this->assertSame($expected, $list->render(['one', 'two', 'three']));
	}

	/**
	 *
	 */
	public function testNestedLists()
	{
		$output = Mockery::mock('mako\cli\output\Output');

		$list = new UnorderedList($output);

		$expected  = '';
		$expected .= '* one' . PHP_EOL;
		$expected .= '* two' . PHP_EOL;
		$expected .= '* three' . PHP_EOL;
		$expected .= '  * one' . PHP_EOL;
		$expected .= '  * two' . PHP_EOL;
		$expected .= '  * three' . PHP_EOL;
		$expected .= '* four' . PHP_EOL;

		$this->assertSame($expected, $list->render(['one', 'two', 'three', ['one', 'two', 'three'], 'four']));
	}

	/**
	 *
	 */
	public function testCustomMarker()
	{
		$output = Mockery::mock('mako\cli\output\Output');

		$list = new UnorderedList($output);

		$expected  = '';
		$expected .= '# one' . PHP_EOL;
		$expected .= '# two' . PHP_EOL;
		$expected .= '# three' . PHP_EOL;

		$this->assertSame($expected, $list->render(['one', 'two', 'three'], '#'));
	}

	/**
	 *
	 */
	public function testDraw()
	{
		$output = Mockery::mock('mako\cli\output\Output');

		$list = new UnorderedList($output);

		$expected  = '';
		$expected .= '* one' . PHP_EOL;
		$expected .= '* two' . PHP_EOL;
		$expected .= '* three' . PHP_EOL;

		$output->shouldReceive('write')->once()->with($expected, 1);

		$list->draw(['one', 'two', 'three']);
	}

	/**
	 *
	 */
	public function testDrawWithCustomMarker()
	{
		$output = Mockery::mock('mako\cli\output\Output');

		$list = new UnorderedList($output);

		$expected  = '';
		$expected .= '# one' . PHP_EOL;
		$expected .= '# two' . PHP_EOL;
		$expected .= '# three' . PHP_EOL;

		$output->shouldReceive('write')->once()->with($expected, 1);

		$list->draw(['one', 'two', 'three'], '#');
	}
}
