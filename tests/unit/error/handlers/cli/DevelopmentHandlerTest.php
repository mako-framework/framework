<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\error\handlers\cli;

use ErrorException;
use mako\cli\output\formatter\Formatter;
use mako\cli\output\Output;
use mako\error\handlers\cli\DevelopmentHandler;
use mako\tests\TestCase;
use Mockery;
use RuntimeException;

/**
 * @group unit
 */
class DevelopmentHandlerTest extends TestCase
{
	/**
	 * Returns output string.
	 *
	 * @param string $type    Error type
	 * @param string $message Error message
	 * @param string $trace   Exception trace
	 */
	protected function getOutputString($type, $message, $trace)
	{
		return '<bg_red><white>' . $type . ': ' . $message . PHP_EOL . PHP_EOL . $trace . PHP_EOL . '</white></bg_red>';
	}

	/**
	 *
	 */
	public function testErrorException()
	{
		$formatter = Mockery::mock(Formatter::class);

		$formatter->shouldReceive('escape')->once()->with('Fatal Error')->andReturn('Fatal Error');

		$formatter->shouldReceive('escape')->once()->with('fail')->andReturn('fail');

		$formatter->shouldReceive('escape')->once()->with('foobar.php')->andReturn('foobar.php');

		$formatter->shouldReceive('escape')->once()->with(123)->andReturn('123');

		$formatter->shouldReceive('escape')->once()/*->with(backtrace)*/->andReturn('backtrace');

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->times(5)->andReturn($formatter);

		$output->shouldReceive('errorLn')->once()->with($this->getOutputString('Fatal Error', 'fail' . PHP_EOL . PHP_EOL . 'Error location: foobar.php on line 123', 'backtrace'));

		//

		$handler = new DevelopmentHandler($output);

		$this->assertFalse($handler->handle(new ErrorException('fail', E_ERROR, 1, 'foobar.php', 123)));
	}

	/**
	 *
	 */
	public function testRuntimeException()
	{
		$formatter = Mockery::mock(Formatter::class);

		$formatter->shouldReceive('escape')->once()->with('RuntimeException')->andReturn('RuntimeException');

		$formatter->shouldReceive('escape')->once()->with('fail')->andReturn('fail');

		$formatter->shouldReceive('escape')->once()->with(__FILE__)->andReturn(__FILE__);

		$formatter->shouldReceive('escape')->once()/*->with(error line)*/->andReturn('123');

		$formatter->shouldReceive('escape')->once()/*->with(backtrace)*/->andReturn('backtrace');

		//

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->times(5)->andReturn($formatter);

		$output->shouldReceive('errorLn')->once()->with($this->getOutputString('RuntimeException', 'fail' . PHP_EOL . PHP_EOL . 'Error location: ' . __FILE__ . ' on line 123', 'backtrace'));

		//

		$handler = new DevelopmentHandler($output);

		$this->assertFalse($handler->handle(new RuntimeException('fail', 123)));
	}
}
