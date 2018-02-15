<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output;

use Mockery;

use mako\cli\output\Output;
use mako\tests\TestCase;

/**
 * @group unit
 */
class OutputTest extends TestCase
{
	/**
	 *
	 */
	public function getWriter()
	{
		return Mockery::mock('mako\cli\output\writer\WriterInterface');
	}

	/**
	 *
	 */
	public function getFormatter()
	{
		return Mockery::mock('mako\cli\output\formatter\FormatterInterface');
	}

	/**
	 *
	 */
	public function testHasAnsiSupport()
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$output = new Output($std, $err, null, false);

		$this->assertFalse($output->hasAnsiSupport());

		$output = new Output($std, $err, null, true);

		$this->assertTrue($output->hasAnsiSupport());

		$output = new Output($std, $err);

		$this->assertInternalType('boolean', $output->hasAnsiSupport());
	}

	/**
	 *
	 */
	public function testWrite()
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$std->shouldReceive('write')->once()->with('hello, world!');

		$output = new Output($std, $err);

		$output->write('hello, world!');
	}

	/**
	 *
	 */
	public function testWriteWithErrorParam()
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$err->shouldReceive('write')->once()->with('hello, world!');

		$output = new Output($std, $err);

		$output->write('hello, world!', Output::ERROR);
	}

	/**
	 *
	 */
	public function testWriteLn()
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$std->shouldReceive('write')->once()->with('hello, world!' . PHP_EOL);

		$output = new Output($std, $err);

		$output->writeLn('hello, world!');
	}

	/**
	 *
	 */
	public function testWriteLnWithErrorParam()
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$err->shouldReceive('write')->once()->with('hello, world!' . PHP_EOL);

		$output = new Output($std, $err);

		$output->writeLn('hello, world!', Output::ERROR);
	}

	/**
	 *
	 */
	public function testError()
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$err->shouldReceive('write')->once()->with('hello, world!');

		$output = new Output($std, $err);

		$output->error('hello, world!');
	}

	/**
	 *
	 */
	public function testErrorLn()
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$err->shouldReceive('write')->once()->with('hello, world!' . PHP_EOL);

		$output = new Output($std, $err);

		$output->errorLn('hello, world!');
	}

	/**
	 *
	 */
	public function testClear()
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$std->shouldReceive('write')->once()->with("\e[H\e[2J");

		$output = new Output($std, $err);

		$output->clear("\e[H\e[2J");
	}

	/**
	 *
	 */
	public function testMute()
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$std->shouldReceive('write')->never()->with('hello, world!');

		$output = new Output($std, $err);

		$output->mute();

		$this->assertTrue($output->isMuted());

		$output->write('hello, world!');
	}

	/**
	 *
	 */
	public function testUnmute()
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$std->shouldReceive('write')->once()->with('hello, world!');

		$output = new Output($std, $err);

		$output->mute();

		$this->assertTrue($output->isMuted());

		$output->write('hello, world!');

		$output->unmute();

		$this->assertFalse($output->isMuted());

		$output->write('hello, world!');
	}

	/**
	 *
	 */
	public function testGetNullFormatter()
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$output = new Output($std, $err);

		$this->assertSame(null, $output->getFormatter());
	}

	/**
	 *
	 */
	public function testGetFormatter()
	{
		$std       = $this->getWriter();
		$err       = $this->getWriter();
		$formatter = $this->getFormatter();

		$output = new Output($std, $err, $formatter);

		$this->assertInstanceOf('mako\cli\output\formatter\FormatterInterface', $output->getFormatter());
	}

	/**
	 *
	 */
	public function testSetFormatter()
	{
		$std       = $this->getWriter();
		$err       = $this->getWriter();
		$formatter = $this->getFormatter();

		$output = new Output($std, $err);

		$this->assertSame(null, $output->getFormatter());

		$output->setFormatter($this->getFormatter());

		$this->assertInstanceOf('mako\cli\output\formatter\FormatterInterface', $output->getFormatter());
	}

	/**
	 *
	 */
	public function testWriteWithFormatterWithAnsiSupport()
	{
		$std       = $this->getWriter();
		$err       = $this->getWriter();
		$formatter = $this->getFormatter();

		$formatter->shouldReceive('format')->once()->with('hello, world!')->andReturn('formatted');

		$std->shouldReceive('write')->once()->with('formatted');

		$std->shouldReceive('isDirect')->once()->andReturn(true);

		$output = new Output($std, $err, $formatter, true);

		$output->write('hello, world!');
	}

	/**
	 *
	 */
	public function testWriteWithFormatterWithoutAnsiSupport()
	{
		$std       = $this->getWriter();
		$err       = $this->getWriter();
		$formatter = $this->getFormatter();

		$formatter->shouldReceive('stripTags')->once()->with('hello, world!')->andReturn('stripped');

		$std->shouldReceive('isDirect')->never();

		$formatter->shouldReceive('format')->once()->with('stripped')->andReturn('formatted');

		$std->shouldReceive('write')->once()->with('formatted');

		$output = new Output($std, $err, $formatter, false);

		$output->write('hello, world!');
	}

	/**
	 *
	 */
	public function testWriteWithFormatterAndRedirectedOutput()
	{
		$std       = $this->getWriter();
		$err       = $this->getWriter();
		$formatter = $this->getFormatter();

		$std->shouldReceive('isDirect')->once()->andReturn(false);

		$formatter->shouldReceive('stripTags')->once()->with('hello, world!')->andReturn('stripped');

		$formatter->shouldReceive('format')->once()->with('stripped')->andReturn('formatted');

		$std->shouldReceive('write')->once()->with('formatted');

		$output = new Output($std, $err, $formatter);

		$output->write('hello, world!');
	}
}
