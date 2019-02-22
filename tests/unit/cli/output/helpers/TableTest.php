<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use mako\cli\output\formatter\FormatterInterface;
use mako\cli\output\helpers\Table;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class TableTest extends TestCase
{
	/**
	 *
	 */
	public function testBasicTable(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$table = new Table($output);

		$expected  = '';
		$expected .= '---------' . PHP_EOL;
		$expected .= '| Col1  |' . PHP_EOL;
		$expected .= '---------' . PHP_EOL;
		$expected .= '| Cell1 |' . PHP_EOL;
		$expected .= '---------' . PHP_EOL;

		$this->assertSame($expected, $table->render(['Col1'], [['Cell1']]));
	}

	/**
	 *
	 */
	public function testTableWithMultipleRows(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$table = new Table($output);

		$expected  = '';
		$expected .= '---------' . PHP_EOL;
		$expected .= '| Col1  |' . PHP_EOL;
		$expected .= '---------' . PHP_EOL;
		$expected .= '| Cell1 |' . PHP_EOL;
		$expected .= '| Cell1 |' . PHP_EOL;
		$expected .= '---------' . PHP_EOL;

		$this->assertSame($expected, $table->render(['Col1'], [['Cell1'], ['Cell1']]));
	}

	/**
	 *
	 */
	public function testTableWithMultipleColumns(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$table = new Table($output);

		$expected  = '';
		$expected .= '-----------------' . PHP_EOL;
		$expected .= '| Col1  | Col2  |' . PHP_EOL;
		$expected .= '-----------------' . PHP_EOL;
		$expected .= '| Cell1 | Cell2 |' . PHP_EOL;
		$expected .= '-----------------' . PHP_EOL;

		$this->assertSame($expected, $table->render(['Col1', 'Col2'], [['Cell1', 'Cell2']]));
	}

	/**
	 *
	 */
	public function testTableWithMultipleColumnsAndRows(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$table = new Table($output);

		$expected  = '';
		$expected .= '-----------------' . PHP_EOL;
		$expected .= '| Col1  | Col2  |' . PHP_EOL;
		$expected .= '-----------------' . PHP_EOL;
		$expected .= '| Cell1 | Cell2 |' . PHP_EOL;
		$expected .= '| Cell1 | Cell2 |' . PHP_EOL;
		$expected .= '-----------------' . PHP_EOL;

		$this->assertSame($expected, $table->render(['Col1', 'Col2'], [['Cell1', 'Cell2'], ['Cell1', 'Cell2']]));
	}

	/**
	 *
	 */
	public function testStyledContent(): void
	{
		$output = Mockery::mock(Output::class);

		$formatter = Mockery::mock(FormatterInterface::class);

		$formatter->shouldReceive('stripTags')->times(2)->with('<blue>Col1</blue>')->andReturn('Col1');

		$formatter->shouldReceive('stripTags')->times(2)->with('Cell1')->andReturn('Cell1');

		$output->shouldReceive('getFormatter')->andReturn($formatter);

		$table = new Table($output);

		$expected  = '';
		$expected .= '---------' . PHP_EOL;
		$expected .= '| <blue>Col1</blue>  |' . PHP_EOL;
		$expected .= '---------' . PHP_EOL;
		$expected .= '| Cell1 |' . PHP_EOL;
		$expected .= '---------' . PHP_EOL;

		$this->assertSame($expected, $table->render(['<blue>Col1</blue>'], [['Cell1']]));
	}

	/**
	 *
	 */
	public function testDraw(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$expected  = '';
		$expected .= '---------' . PHP_EOL;
		$expected .= '| Col1  |' . PHP_EOL;
		$expected .= '---------' . PHP_EOL;
		$expected .= '| Cell1 |' . PHP_EOL;
		$expected .= '---------' . PHP_EOL;

		$output->shouldReceive('write')->once()->with($expected, Output::STANDARD);

		$table = new Table($output);

		$table->draw(['Col1'], [['Cell1']]);
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testInvalidInput(): void
	{
		$output = Mockery::mock(Output::class);

		$table = new Table($output);

		$table->render(['Col1'], [['Cell1', 'Cell2']]);
	}
}
