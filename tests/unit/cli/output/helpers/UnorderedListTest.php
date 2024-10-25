<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use mako\cli\output\helpers\UnorderedList;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class UnorderedListTest extends TestCase
{
	/**
	 *
	 */
	public function testBasicList(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

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
	public function testNestedLists(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

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
	public function testCustomMarker(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

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
	public function testDraw(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

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
	public function testDrawWithCustomMarker(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$list = new UnorderedList($output);

		$expected  = '';
		$expected .= '# one' . PHP_EOL;
		$expected .= '# two' . PHP_EOL;
		$expected .= '# three' . PHP_EOL;

		$output->shouldReceive('write')->once()->with($expected, 1);

		$list->draw(['one', 'two', 'three'], '#');
	}
}
