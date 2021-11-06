<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output;

use mako\cli\Environment;
use mako\cli\output\formatter\FormatterInterface;
use mako\cli\output\Output;
use mako\cli\output\writer\WriterInterface;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class OutputTest extends TestCase
{
	/**
	 * @return \mako\cli\output\writer\WriterInterface|\Mockery\MockInterface
	 */
	public function getWriter()
	{
		return Mockery::mock(WriterInterface::class);
	}

	/**
	 * @return \mako\cli\output\formatter\FormatterInterface|\Mockery\MockInterface
	 */
	public function getFormatter()
	{
		return Mockery::mock(FormatterInterface::class);
	}

	/**
	 *
	 */
	public function testGetWriter(): void
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$output = new Output($std, $err);

		$this->assertInstanceOf(WriterInterface::class, $output->getWriter());
		$this->assertInstanceOf(WriterInterface::class, $output->getWriter(Output::STANDARD));
		$this->assertInstanceOf(WriterInterface::class, $output->getWriter(Output::ERROR));
	}

	/**
	 *
	 */
	public function testGetEnvironment(): void
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$output = new Output($std, $err);

		$this->assertInstanceOf(Environment::class, $output->getEnvironment());

		//

		$env = new Environment;

		$output = new Output($std, $err, null, $env);

		$this->assertInstanceOf(Environment::class, $output->getEnvironment());

		$this->assertSame($env, $output->getEnvironment());
	}

	/**
	 *
	 */
	public function testWrite(): void
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
	public function testWriteWithErrorParam(): void
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
	public function testWriteLn(): void
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
	public function testWriteLnWithErrorParam(): void
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
	public function testError(): void
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
	public function testErrorLn(): void
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
	public function testClearWithAnsiSupport(): void
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$std->shouldReceive('write')->once()->with("\e[H\e[2J");

		/** @var \mako\cli\Environment|\Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(true);

		$output = new Output($std, $err, null, $env);

		$output->clear();
	}

	/**
	 *
	 */
	public function testClearWithNoAnsiSupport(): void
 	{
 		$std = $this->getWriter();
 		$err = $this->getWriter();

 		$std->shouldReceive('write')->never();

		/** @var \mako\cli\Environment|\Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(false);

 		$output = new Output($std, $err, null, $env);

 		$output->clear();
 	}

	/**
	 *
	 */
	public function testClearLineWithAnsiSupport(): void
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$std->shouldReceive('write')->once()->with("\r\33[2K");

		/** @var \mako\cli\Environment|\Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(true);

		$output = new Output($std, $err, null, $env);

		$output->clearLine();
	}

	/**
	 *
	 */
	public function testClearLineWithNoAnsiSupport(): void
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$std->shouldReceive('write')->never();

		/** @var \mako\cli\Environment|\Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(false);

		$output = new Output($std, $err, null, $env);

		$output->clearLine();
	}

	/**
	 *
	 */
	public function testClearLinesWithAnsiSupport(): void
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$std->shouldReceive('write')->once()->with("\033[F");

		$std->shouldReceive('write')->times(2)->with("\r\33[2K");

		/** @var \mako\cli\Environment|\Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(true);

		$output = new Output($std, $err, null, $env);

		$output->clearLines(2);
	}

	/**
	 *
	 */
	public function testClearLinesWithNoAnsiSupport(): void
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$std->shouldReceive('write')->never();

		/** @var \mako\cli\Environment|\Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(false);

		$output = new Output($std, $err, null, $env);

		$output->clearLines(2);
	}

	/**
	 *
	 */
	public function testMute(): void
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
	public function testUnmute(): void
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
	public function testGetNullFormatter(): void
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$output = new Output($std, $err);

		$this->assertSame(null, $output->getFormatter());
	}

	/**
	 *
	 */
	public function testGetFormatter(): void
	{
		$std       = $this->getWriter();
		$err       = $this->getWriter();
		$formatter = $this->getFormatter();

		$output = new Output($std, $err, $formatter);

		$this->assertInstanceOf(FormatterInterface::class, $output->getFormatter());
	}

	/**
	 *
	 */
	public function testSetFormatter(): void
	{
		$std       = $this->getWriter();
		$err       = $this->getWriter();
		$formatter = $this->getFormatter();

		$output = new Output($std, $err);

		$this->assertSame(null, $output->getFormatter());

		$output->setFormatter($formatter);

		$this->assertInstanceOf(FormatterInterface::class, $output->getFormatter());
	}

	/**
	 *
	 */
	public function testWriteWithFormatterWithAnsiSupport(): void
	{
		$std       = $this->getWriter();
		$err       = $this->getWriter();
		$formatter = $this->getFormatter();

		$formatter->shouldReceive('format')->once()->with('hello, world!')->andReturn('formatted');

		$std->shouldReceive('write')->once()->with('formatted');

		$std->shouldReceive('isDirect')->once()->andReturn(true);

		/** @var \mako\cli\Environment|\Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(true);

		$output = new Output($std, $err, $formatter, $env);

		$output->write('hello, world!');
	}

	/**
	 *
	 */
	public function testWriteWithFormatterWithoutAnsiSupport(): void
	{
		$std       = $this->getWriter();
		$err       = $this->getWriter();
		$formatter = $this->getFormatter();

		$formatter->shouldReceive('stripTags')->once()->with('hello, world!')->andReturn('stripped');

		$std->shouldReceive('isDirect')->never();

		$formatter->shouldReceive('format')->once()->with('stripped')->andReturn('formatted');

		$std->shouldReceive('write')->once()->with('formatted');

		/** @var \mako\cli\Environment|\Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(false);

		$output = new Output($std, $err, $formatter, $env);

		$output->write('hello, world!');
	}

	/**
	 *
	 */
	public function testWriteWithFormatterAndRedirectedOutput(): void
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

	/**
	 *
	 */
	public function testDump(): void
	{
		$std = $this->getWriter();
		$err = $this->getWriter();

		$std->shouldReceive('write')->once()->with("'foobar'" . PHP_EOL);

		$output = new Output($std, $err);

		$output->dump('foobar');
	}
}
