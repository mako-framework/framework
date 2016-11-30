<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\cli\output\helpers\Table;

/**
 * @group unit
 */
class TableTest extends PHPUnit_Framework_TestCase
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
	public function testBasicTable()
	{
		$output = Mockery::mock('mako\cli\output\Output');

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
		$output = Mockery::mock('mako\cli\output\Output');

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
		$output = Mockery::mock('mako\cli\output\Output');

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
		$output = Mockery::mock('mako\cli\output\Output');

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
		$output = Mockery::mock('mako\cli\output\Output');

		$formatter = Mockery::mock('mako\cli\output\formatter\FormatterInterface');

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
		$output = Mockery::mock('mako\cli\output\Output');

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
		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('getFormatter')->once()->andReturn(null);

		$table = new Table($output);

		$table->render(['Col1'], [['Cell1', 'Cell2']]);
	}
}
