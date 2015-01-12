<?php

namespace mako\tests\unit\cli\output;

use mako\cli\output\Output;

use Mockery as m;

use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */

class OutputTest extends PHPUnit_Framework_TestCase
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

	public function getWriter()
	{
		return m::mock('mako\cli\output\writer\WriterInterface');
	}

	/**
	 * 
	 */

	public function getFormatter()
	{
		return m::mock('mako\cli\output\formatter\FormatterInterface');
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

	public function testMute()
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$std->shouldReceive('write')->never()->with('hello, world!');

		$output = new Output($std, $err);

		$output->mute();

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

		$output->write('hello, world!');

		$output->unmute();

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

	public function testWriteWithFormatter()
	{
		$std       = $this->getWriter();
		$err       = $this->getWriter();
		$formatter = $this->getFormatter();

		$formatter->shouldReceive('format')->once()->with('hello, world!')->andReturn('formatted');

		$std->shouldReceive('write')->once()->with('formatted');

		$output = new Output($std, $err, $formatter);

		$output->write('hello, world!');
	}
}