<?php

namespace mako\tests\unit\cli\output\helpers;

use mako\cli\output\helpers\Table;

use Mockery as m;

/**
 * @group unit
 */

class TableTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function testBasicTable()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('getFormatter')->once()->andReturn(null);

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

	public function testTableWithMultipleRows()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('getFormatter')->once()->andReturn(null);

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

	public function testTableWithMultipleColumns()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('getFormatter')->once()->andReturn(null);

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

	public function testTableWithMultipleColumnsAndRows()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('getFormatter')->once()->andReturn(null);

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

	public function testStyledContent()
	{
		$output = m::mock('mako\cli\output\Output');

		$formatter = m::mock('mako\cli\output\formatter\FormatterInterface');

		$formatter->shouldReceive('strip')->times(2)->with('<blue>Col1</blue>')->andReturn('Col1');

		$formatter->shouldReceive('strip')->times(2)->with('Cell1')->andReturn('Cell1');

		$output->shouldReceive('getFormatter')->once()->andReturn($formatter);

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

	public function testDraw()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('getFormatter')->once()->andReturn(null);

		$expected  = '';
		$expected .= '---------' . PHP_EOL;
		$expected .= '| Col1  |' . PHP_EOL;
		$expected .= '---------' . PHP_EOL;
		$expected .= '| Cell1 |' . PHP_EOL;
		$expected .= '---------' . PHP_EOL;

		$output->shouldReceive('write')->once()->with($expected, 1);

		$table = new Table($output);

		$table->draw(['Col1'], [['Cell1']]);
	}

	/**
	 * @expectedException \RuntimeException
	 */

	public function testInvalidInput()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('getFormatter')->once()->andReturn(null);

		$table = new Table($output);

		$table->render(['Col1'], [['Cell1', 'Cell2']]);
	}
}