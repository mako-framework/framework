<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\output\components\Frame;
use mako\cli\output\components\frame\AsciiTheme;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class FrameTest extends TestCase
{
	/**
	 *
	 */
	public function testFrame(): void
	{
		$output = Mockery::mock(Output::class);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

		$table = new Frame($output, width: 50);

		$expected  = '';
		$expected .= '┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓' . PHP_EOL;
		$expected .= '┃ This is the content                            ┃' . PHP_EOL;
		$expected .= '┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛' . PHP_EOL;

		$this->assertSame($expected, $table->render('This is the content'));
	}

	/**
	 *
	 */
	public function testFrameWithTitle(): void
	{
		$output = Mockery::mock(Output::class);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

		$table = new Frame($output, width: 50);

		$expected  = '';
		$expected .= '┏━ Title ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓' . PHP_EOL;
		$expected .= '┃ This is the content                            ┃' . PHP_EOL;
		$expected .= '┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛' . PHP_EOL;

		$this->assertSame($expected, $table->render('This is the content', 'Title'));
	}

	/**
	 *
	 */
	public function testFameWithAsciiBorder(): void
	{
		$output = Mockery::mock(Output::class);

		(function (): void {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

		$table = new Frame($output, new AsciiTheme, width: 50);

		$expected  = '';
		$expected .= '+------------------------------------------------+' . PHP_EOL;
		$expected .= '| This is the content                            |' . PHP_EOL;
		$expected .= '+------------------------------------------------+' . PHP_EOL;

		$this->assertSame($expected, $table->render('This is the content'));
	}
}
