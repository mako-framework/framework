<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use mako\cli\output\helpers\OrderedList;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class OrderedListTest extends TestCase
{
	/**
	 *
	 */
	public function testBasicList(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->once()->andReturn(null);

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
	public function testNestedLists(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->once()->andReturn(null);

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
	public function testCustomMarker(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->once()->andReturn(null);

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
	public function testDraw(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->once()->andReturn(null);

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
	public function testDrawWithCustomMarker(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->once()->andReturn(null);

		$list = new OrderedList($output);

		$expected  = '';
		$expected .= '[1] one' . PHP_EOL;
		$expected .= '[2] two' . PHP_EOL;
		$expected .= '[3] three' . PHP_EOL;

		$output->shouldReceive('write')->once()->with($expected, 1);

		$list->draw(['one', 'two', 'three'], '[%s]');
	}
}
