<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\output\components\OrderedList;
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
		$output = Mockery::mock(Output::class);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

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
		$output = Mockery::mock(Output::class);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

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
		$output = Mockery::mock(Output::class);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

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
		$output = Mockery::mock(Output::class);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

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
		$output = Mockery::mock(Output::class);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

		$list = new OrderedList($output);

		$expected  = '';
		$expected .= '[1] one' . PHP_EOL;
		$expected .= '[2] two' . PHP_EOL;
		$expected .= '[3] three' . PHP_EOL;

		$output->shouldReceive('write')->once()->with($expected, 1);

		$list->draw(['one', 'two', 'three'], '[%s]');
	}
}
