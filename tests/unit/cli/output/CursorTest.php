<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output;

use mako\cli\input\reader\ReaderInterface;
use mako\cli\output\Cursor;
use mako\cli\output\writer\WriterInterface;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class CursorTest extends TestCase
{
	/**
	 *
	 */
	public function testUp(): void
	{
		$writer = Mockery::mock(WriterInterface::class);
		$reader = Mockery::mock(ReaderInterface::class);

		$cursor = new Cursor($writer, $reader);

		$writer->shouldReceive('write')->once()->with("\033[1A");
		$writer->shouldReceive('write')->once()->with("\033[2A");

		$cursor->up();
		$cursor->up(2);
	}

	/**
	 *
	 */
	public function testDown(): void
	{
		$writer = Mockery::mock(WriterInterface::class);
		$reader = Mockery::mock(ReaderInterface::class);

		$cursor = new Cursor($writer, $reader);

		$writer->shouldReceive('write')->once()->with("\033[1B");
		$writer->shouldReceive('write')->once()->with("\033[2B");

		$cursor->down();
		$cursor->down(2);
	}

	/**
	 *
	 */
	public function testLeft(): void
	{
		$writer = Mockery::mock(WriterInterface::class);
		$reader = Mockery::mock(ReaderInterface::class);

		$cursor = new Cursor($writer, $reader);

		$writer->shouldReceive('write')->once()->with("\033[1D");
		$writer->shouldReceive('write')->once()->with("\033[2D");

		$cursor->left();
		$cursor->left(2);
	}

	/**
	 *
	 */
	public function testRight(): void
	{
		$writer = Mockery::mock(WriterInterface::class);
		$reader = Mockery::mock(ReaderInterface::class);

		$cursor = new Cursor($writer, $reader);

		$writer->shouldReceive('write')->once()->with("\033[1C");
		$writer->shouldReceive('write')->once()->with("\033[2C");

		$cursor->right();
		$cursor->right(2);
	}

	/**
	 *
	 */
	public function testMoveTo(): void
	{
		$writer = Mockery::mock(WriterInterface::class);
		$reader = Mockery::mock(ReaderInterface::class);

		$cursor = new Cursor($writer, $reader);

		$writer->shouldReceive('write')->once()->with("\033[6;9H");

		$cursor->moveTo(6, 9);
	}

	/**
	 *
	 */
	public function testClearLines(): void
	{
		$writer = Mockery::mock(WriterInterface::class);
		$reader = Mockery::mock(ReaderInterface::class);

		$cursor = new Cursor($writer, $reader);

		$writer->shouldReceive('write')->times(2)->with("\033[1A");
		$writer->shouldReceive('write')->times(3)->with("\r\33[2K");

		$cursor->clearLines(3);
	}
}
