<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output;

use mako\cli\Environment;
use mako\cli\output\Cursor;
use mako\cli\output\formatter\FormatterInterface;
use mako\cli\output\Output;
use mako\cli\output\writer\WriterInterface;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class OutputTest extends TestCase
{
	/**
	 * @return Mockery\MockInterface|WriterInterface
	 */
	public function getWriter()
	{
		return Mockery::mock(WriterInterface::class);
	}

	/**
	 * @return FormatterInterface|Mockery\MockInterface
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
		$output = new Output;

		$this->assertInstanceOf(WriterInterface::class, $output->getWriter());
		$this->assertInstanceOf(WriterInterface::class, $output->getWriter(Output::STANDARD));
		$this->assertInstanceOf(WriterInterface::class, $output->getWriter(Output::ERROR));
		$this->assertInstanceOf(WriterInterface::class, $output->standard);
		$this->assertInstanceOf(WriterInterface::class, $output->error);
	}

	/**
	 *
	 */
	public function testGetEnvironment(): void
	{
		$output = new Output;

		$this->assertInstanceOf(Environment::class, $output->environment);
		$this->assertInstanceOf(Environment::class, $output->getEnvironment());

		//

		$env = new Environment;

		$output = new Output(environment: $env);

		$this->assertInstanceOf(Environment::class, $output->environment);
		$this->assertInstanceOf(Environment::class, $output->getEnvironment());

		$this->assertSame($env, $output->environment);
		$this->assertSame($env, $output->getEnvironment());
	}

	/**
	 *
	 */
	public function testGetCursor(): void
	{
		$output = new Output;

		$this->assertNull($output->cursor);
		$this->assertNull($output->getCursor());

		//

		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$output = new Output(cursor: $cursor);

		$this->assertInstanceOf(Cursor::class, $output->cursor);
		$this->assertInstanceOf(Cursor::class, $output->getCursor());

		$this->assertSame($cursor, $output->cursor);
		$this->assertSame($cursor, $output->getCursor());
	}

	/**
	 *
	 */
	public function testWrite(): void
	{
		$std = $this->getWriter();

		$std->shouldReceive('write')->once()->with('hello, world!');

		$output = new Output(standard: $std);

		$output->write('hello, world!');
	}

	/**
	 *
	 */
	public function testWriteWithErrorParam(): void
	{
		$err = $this->getWriter();

		$err->shouldReceive('write')->once()->with('hello, world!');

		$output = new Output(error: $err);

		$output->write('hello, world!', Output::ERROR);
	}

	/**
	 *
	 */
	public function testWriteLn(): void
	{
		$std = $this->getWriter();

		$std->shouldReceive('write')->once()->with('hello, world!' . PHP_EOL);

		$output = new Output(standard: $std);

		$output->writeLn('hello, world!');
	}

	/**
	 *
	 */
	public function testWriteLnWithErrorParam(): void
	{
		$err = $this->getWriter();

		$err->shouldReceive('write')->once()->with('hello, world!' . PHP_EOL);

		$output = new Output(error: $err);

		$output->writeLn('hello, world!', Output::ERROR);
	}

	/**
	 *
	 */
	public function testError(): void
	{
		$err = $this->getWriter();

		$err->shouldReceive('write')->once()->with('hello, world!');

		$output = new Output(error: $err);

		$output->error('hello, world!');
	}

	/**
	 *
	 */
	public function testErrorLn(): void
	{
		$err = $this->getWriter();

		$err->shouldReceive('write')->once()->with('hello, world!' . PHP_EOL);

		$output = new Output(error: $err);

		$output->errorLn('hello, world!');
	}

	/**
	 *
	 */
	public function testClearWithAnsiSupport(): void
	{
		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('clearScreen')->once();

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(true);

		$output = new Output(environment: $env, cursor: $cursor);

		$output->clear();
	}

	/**
	 *
	 */
	public function testClearWithNoAnsiSupport(): void
	{
		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('clearScreen')->never();

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(false);

 		$output = new Output(environment: $env, cursor: $cursor);

 		$output->clear();
 	}

	/**
	 *
	 */
	public function testClearLineWithAnsiSupport(): void
	{
		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('clearLine')->once();

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(true);

		$output = new Output(environment: $env, cursor: $cursor);

		$output->clearLine();
	}

	/**
	 *
	 */
	public function testClearLineWithNoAnsiSupport(): void
	{
		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('clearLine')->never();

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(false);

		$output = new Output(environment: $env, cursor: $cursor);

		$output->clearLine();
	}

	/**
	 *
	 */
	public function testClearLinesWithAnsiSupport(): void
	{
		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('clearLines')->once()->with(2);

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(true);

		$output = new Output(environment: $env, cursor: $cursor);

		$output->clearLines(2);
	}

	/**
	 *
	 */
	public function testClearLinesWithNoAnsiSupport(): void
	{
		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('clearLines')->never();

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(false);

		$output = new Output(environment: $env, cursor: $cursor);

		$output->clearLines(2);
	}

	/**
	 *
	 */
	public function testHideCursorWithAnsiSupport(): void
	{
		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('hide')->once();

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(true);

		$output = new Output(environment: $env, cursor: $cursor);

		$output->hideCursor();
	}

	/**
	 *
	 */
	public function testHideCursorWithNoAnsiSupport(): void
	{
		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('hide')->never();

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(false);

		$output = new Output(environment: $env, cursor: $cursor);

		$output->hideCursor();
	}

	/**
	 *
	 */
	public function testShowCursorWithAnsiSupport(): void
	{
		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('show')->once();

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(true);

		$output = new Output(environment: $env, cursor: $cursor);

		$output->showCursor();
	}

	/**
	 *
	 */
	public function testShowCursorWithNoAnsiSupport(): void
	{
		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('show')->never();

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(false);

		$output = new Output(environment: $env, cursor: $cursor);

		$output->showCursor();
	}

	/**
	 *
	 */
	public function testRestoreCursorWithAnsiSupport(): void
	{
		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('restore')->once();

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(true);

		$output = new Output(environment: $env, cursor: $cursor);

		$output->restoreCursor();
	}

	/**
	 *
	 */
	public function testRestoreCursorWithNoAnsiSupport(): void
	{
		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('restore')->never();

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(false);

		$output = new Output(environment: $env, cursor: $cursor);

		$output->restoreCursor();
	}

	/**
	 *
	 */
	public function testMute(): void
	{
		$std = $this->getWriter();

		$std->shouldReceive('write')->never()->with('hello, world!');

		$output = new Output(standard: $std);

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

		$std->shouldReceive('write')->once()->with('hello, world!');

		$output = new Output(standard: $std);

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
		$output = new Output;

		$this->assertNull($output->formatter);
		$this->assertNull($output->getFormatter());
	}

	/**
	 *
	 */
	public function testGetFormatter(): void
	{
		$formatter = $this->getFormatter();

		$output = new Output(formatter: $formatter);

		$this->assertInstanceOf(FormatterInterface::class, $output->formatter);
		$this->assertInstanceOf(FormatterInterface::class, $output->getFormatter());
	}

	/**
	 *
	 */
	public function testSetFormatter(): void
	{
		$formatter = $this->getFormatter();

		$output = new Output;

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
		$formatter = $this->getFormatter();

		$formatter->shouldReceive('format')->once()->with('hello, world!')->andReturn('formatted');

		$std->shouldReceive('write')->once()->with('formatted');

		$std->shouldReceive('isDirect')->once()->andReturn(true);

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(true);

		$env->shouldReceive('noColor')->andReturn(false);

		$output = new Output(standard: $std, environment: $env, formatter: $formatter);

		$output->write('hello, world!');
	}

	/**
	 *
	 */
	public function testWriteWithFormatterWithoutAnsiSupport(): void
	{
		$std       = $this->getWriter();
		$formatter = $this->getFormatter();

		$formatter->shouldReceive('stripTags')->once()->with('hello, world!')->andReturn('stripped');

		$std->shouldReceive('isDirect')->never();

		$formatter->shouldReceive('format')->once()->with('stripped')->andReturn('formatted');

		$std->shouldReceive('write')->once()->with('formatted');

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(false);

		$output = new Output(standard: $std, environment: $env, formatter: $formatter);

		$output->write('hello, world!');
	}

	/**
	 *
	 */
	public function testWriteWithFormatterWithAnsiSupportAndNoColor(): void
	{
		$std       = $this->getWriter();
		$formatter = $this->getFormatter();

		$formatter->shouldReceive('stripTags')->once()->with('hello, world!')->andReturn('stripped');

		$std->shouldReceive('isDirect')->never();

		$formatter->shouldReceive('format')->once()->with('stripped')->andReturn('formatted');

		$std->shouldReceive('write')->once()->with('formatted');

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->andReturn(true);

		$env->shouldReceive('noColor')->andReturn(true);

		$output = new Output(standard: $std, environment: $env, formatter: $formatter);

		$output->write('hello, world!');
	}

	/**
	 *
	 */
	public function testWriteWithFormatterAndRedirectedOutput(): void
	{
		$std       = $this->getWriter();
		$formatter = $this->getFormatter();

		$std->shouldReceive('isDirect')->once()->andReturn(false);

		$formatter->shouldReceive('stripTags')->once()->with('hello, world!')->andReturn('stripped');

		$formatter->shouldReceive('format')->once()->with('stripped')->andReturn('formatted');

		$std->shouldReceive('write')->once()->with('formatted');

		$output = new Output(standard: $std, formatter: $formatter);

		$output->write('hello, world!');
	}

	/**
	 *
	 */
	public function testDump(): void
	{
		$std = $this->getWriter();

		$std->shouldReceive('write')->once()->with("'foobar'" . PHP_EOL);

		$output = new Output(standard: $std);

		$output->dump('foobar');
	}
}
