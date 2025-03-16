<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\output\components\progress\AsciiTheme;
use mako\cli\output\components\ProgressIterator;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ProgressIteratorTest extends TestCase
{
	/**
	 *
	 */
	public function testProgressWithZeroItems(): void
	{
		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('write')->once()->with("\r0/0 ████████████████████ 100.00% ");
		$output->shouldReceive('write')->once()->with(PHP_EOL);

		$progress = new ProgressIterator($output, []);

		foreach ($progress as $_) {
			//
		}
	}

	/**
	 *
	 */
	public function testProgressWithItems(): void
	{
		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('write')->once()->with("\r00/10 ────────────────────   0.00% ");
		$output->shouldReceive('write')->once()->with("\r01/10 ██──────────────────  10.00% ");
		$output->shouldReceive('write')->once()->with("\r02/10 ████────────────────  20.00% ");
		$output->shouldReceive('write')->once()->with("\r03/10 ██████──────────────  30.00% ");
		$output->shouldReceive('write')->once()->with("\r04/10 ████████────────────  40.00% ");
		$output->shouldReceive('write')->once()->with("\r05/10 ██████████──────────  50.00% ");
		$output->shouldReceive('write')->once()->with("\r06/10 ████████████────────  60.00% ");
		$output->shouldReceive('write')->once()->with("\r07/10 ██████████████──────  70.00% ");
		$output->shouldReceive('write')->once()->with("\r08/10 ████████████████────  80.00% ");
		$output->shouldReceive('write')->once()->with("\r09/10 ██████████████████──  90.00% ");
		$output->shouldReceive('write')->once()->with("\r10/10 ████████████████████ 100.00% ");
		$output->shouldReceive('write')->once()->with(PHP_EOL);

		$progress = new class ($output, range(1, 10)) extends ProgressIterator {
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		foreach ($progress as $_) {
			//
		}
	}

	/**
	 *
	 */
	public function testProgressWithUnevenItems(): void
	{
		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('write')->once()->with("\r00/11 ────────────────────   0.00% ");
		$output->shouldReceive('write')->once()->with("\r01/11 █───────────────────   9.09% ");
		$output->shouldReceive('write')->once()->with("\r02/11 ███─────────────────  18.18% ");
		$output->shouldReceive('write')->once()->with("\r03/11 █████───────────────  27.27% ");
		$output->shouldReceive('write')->once()->with("\r04/11 ███████─────────────  36.36% ");
		$output->shouldReceive('write')->once()->with("\r05/11 █████████───────────  45.45% ");
		$output->shouldReceive('write')->once()->with("\r06/11 ██████████──────────  54.55% ");
		$output->shouldReceive('write')->once()->with("\r07/11 ████████████────────  63.64% ");
		$output->shouldReceive('write')->once()->with("\r08/11 ██████████████──────  72.73% ");
		$output->shouldReceive('write')->once()->with("\r09/11 ████████████████────  81.82% ");
		$output->shouldReceive('write')->once()->with("\r10/11 ██████████████████──  90.91% ");
		$output->shouldReceive('write')->once()->with("\r11/11 ████████████████████ 100.00% ");
		$output->shouldReceive('write')->once()->with(PHP_EOL);

		$progress = new class ($output, range(1, 11)) extends ProgressIterator {
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		foreach ($progress as $_) {
			//
		}
	}

	/**
	 *
	 */
	public function testProgressWithCustomWidth(): void
	{
		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('write')->once()->with("\r00/10 ────────────────────────────────────────   0.00% ");
		$output->shouldReceive('write')->once()->with("\r01/10 ████────────────────────────────────────  10.00% ");
		$output->shouldReceive('write')->once()->with("\r02/10 ████████────────────────────────────────  20.00% ");
		$output->shouldReceive('write')->once()->with("\r03/10 ████████████────────────────────────────  30.00% ");
		$output->shouldReceive('write')->once()->with("\r04/10 ████████████████────────────────────────  40.00% ");
		$output->shouldReceive('write')->once()->with("\r05/10 ████████████████████────────────────────  50.00% ");
		$output->shouldReceive('write')->once()->with("\r06/10 ████████████████████████────────────────  60.00% ");
		$output->shouldReceive('write')->once()->with("\r07/10 ████████████████████████████────────────  70.00% ");
		$output->shouldReceive('write')->once()->with("\r08/10 ████████████████████████████████────────  80.00% ");
		$output->shouldReceive('write')->once()->with("\r09/10 ████████████████████████████████████────  90.00% ");
		$output->shouldReceive('write')->once()->with("\r10/10 ████████████████████████████████████████ 100.00% ");
		$output->shouldReceive('write')->once()->with(PHP_EOL);

		$progress = new class ($output, range(1, 10), width: 40) extends ProgressIterator {
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		foreach ($progress as $_) {
			//
		}
	}

	/**
	 *
	 */
	public function testProgressWithCustomTemplates(): void
	{
		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('write')->once()->with("\r00/10 --------------------   0.00% ");
		$output->shouldReceive('write')->once()->with("\r01/10 ==------------------  10.00% ");
		$output->shouldReceive('write')->once()->with("\r02/10 ====----------------  20.00% ");
		$output->shouldReceive('write')->once()->with("\r03/10 ======--------------  30.00% ");
		$output->shouldReceive('write')->once()->with("\r04/10 ========------------  40.00% ");
		$output->shouldReceive('write')->once()->with("\r05/10 ==========----------  50.00% ");
		$output->shouldReceive('write')->once()->with("\r06/10 ============--------  60.00% ");
		$output->shouldReceive('write')->once()->with("\r07/10 ==============------  70.00% ");
		$output->shouldReceive('write')->once()->with("\r08/10 ================----  80.00% ");
		$output->shouldReceive('write')->once()->with("\r09/10 ==================--  90.00% ");
		$output->shouldReceive('write')->once()->with("\r10/10 ==================== 100.00% ");
		$output->shouldReceive('write')->once()->with(PHP_EOL);

		$progress = new class ($output, range(1, 10), theme: new AsciiTheme) extends ProgressIterator {
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		foreach ($progress as $_) {
			//
		}
	}

	/**
	 *
	 */
	public function testProgressWithDescription(): void
	{
		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('write')->once()->with("\rProcessing files: 00/10 ────────────────────   0.00% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 01/10 ██──────────────────  10.00% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 02/10 ████────────────────  20.00% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 03/10 ██████──────────────  30.00% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 04/10 ████████────────────  40.00% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 05/10 ██████████──────────  50.00% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 06/10 ████████████────────  60.00% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 07/10 ██████████████──────  70.00% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 08/10 ████████████████────  80.00% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 09/10 ██████████████████──  90.00% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 10/10 ████████████████████ 100.00% ");
		$output->shouldReceive('write')->once()->with(PHP_EOL);

		$progress = new class ($output, range(1, 10), description: 'Processing files:') extends ProgressIterator {
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		foreach ($progress as $_) {
			//
		}
	}
}
