<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\output\components\progress\AsciiProgressBar;
use mako\cli\output\components\ProgressIterator;
use mako\cli\output\Cursor;
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
		/** @var Cursor|\Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('hide')->once();
		$cursor->shouldReceive('restore')->times(2);

		/** @var \Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getCursor')->andReturn($cursor);

		$output->shouldReceive('write')->once()->with("\r0/0 ████████████████████ 100% ");
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
		/** @var Cursor|\Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('hide')->once();
		$cursor->shouldReceive('restore')->times(2);

		/** @var \Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getCursor')->andReturn($cursor);

		$output->shouldReceive('write')->once()->with("\r00/10 ────────────────────   0% ");
		$output->shouldReceive('write')->once()->with("\r01/10 ██──────────────────  10% ");
		$output->shouldReceive('write')->once()->with("\r02/10 ████────────────────  20% ");
		$output->shouldReceive('write')->once()->with("\r03/10 ██████──────────────  30% ");
		$output->shouldReceive('write')->once()->with("\r04/10 ████████────────────  40% ");
		$output->shouldReceive('write')->once()->with("\r05/10 ██████████──────────  50% ");
		$output->shouldReceive('write')->once()->with("\r06/10 ████████████────────  60% ");
		$output->shouldReceive('write')->once()->with("\r07/10 ██████████████──────  70% ");
		$output->shouldReceive('write')->once()->with("\r08/10 ████████████████────  80% ");
		$output->shouldReceive('write')->once()->with("\r09/10 ██████████████████──  90% ");
		$output->shouldReceive('write')->once()->with("\r10/10 ████████████████████ 100% ");
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
	public function testProgressWithCustomWidth(): void
	{
		/** @var Cursor|\Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('hide')->once();
		$cursor->shouldReceive('restore')->times(2);

		/** @var \Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getCursor')->andReturn($cursor);

		$output->shouldReceive('write')->once()->with("\r00/10 ────────────────────────────────────────   0% ");
		$output->shouldReceive('write')->once()->with("\r01/10 ████────────────────────────────────────  10% ");
		$output->shouldReceive('write')->once()->with("\r02/10 ████████────────────────────────────────  20% ");
		$output->shouldReceive('write')->once()->with("\r03/10 ████████████────────────────────────────  30% ");
		$output->shouldReceive('write')->once()->with("\r04/10 ████████████████────────────────────────  40% ");
		$output->shouldReceive('write')->once()->with("\r05/10 ████████████████████────────────────────  50% ");
		$output->shouldReceive('write')->once()->with("\r06/10 ████████████████████████────────────────  60% ");
		$output->shouldReceive('write')->once()->with("\r07/10 ████████████████████████████────────────  70% ");
		$output->shouldReceive('write')->once()->with("\r08/10 ████████████████████████████████────────  80% ");
		$output->shouldReceive('write')->once()->with("\r09/10 ████████████████████████████████████────  90% ");
		$output->shouldReceive('write')->once()->with("\r10/10 ████████████████████████████████████████ 100% ");
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
		/** @var Cursor|\Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('hide')->once();
		$cursor->shouldReceive('restore')->times(2);

		/** @var \Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getCursor')->andReturn($cursor);

		$output->shouldReceive('write')->once()->with("\r00/10 --------------------   0% ");
		$output->shouldReceive('write')->once()->with("\r01/10 ==------------------  10% ");
		$output->shouldReceive('write')->once()->with("\r02/10 ====----------------  20% ");
		$output->shouldReceive('write')->once()->with("\r03/10 ======--------------  30% ");
		$output->shouldReceive('write')->once()->with("\r04/10 ========------------  40% ");
		$output->shouldReceive('write')->once()->with("\r05/10 ==========----------  50% ");
		$output->shouldReceive('write')->once()->with("\r06/10 ============--------  60% ");
		$output->shouldReceive('write')->once()->with("\r07/10 ==============------  70% ");
		$output->shouldReceive('write')->once()->with("\r08/10 ================----  80% ");
		$output->shouldReceive('write')->once()->with("\r09/10 ==================--  90% ");
		$output->shouldReceive('write')->once()->with("\r10/10 ==================== 100% ");
		$output->shouldReceive('write')->once()->with(PHP_EOL);

		$progress = new class ($output, range(1, 10), progressBar: new AsciiProgressBar) extends ProgressIterator {
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
		/** @var Cursor|\Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('hide')->once();
		$cursor->shouldReceive('restore')->times(2);

		/** @var \Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getCursor')->andReturn($cursor);

		$output->shouldReceive('write')->once()->with("\rProcessing files: 00/10 ────────────────────   0% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 01/10 ██──────────────────  10% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 02/10 ████────────────────  20% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 03/10 ██████──────────────  30% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 04/10 ████████────────────  40% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 05/10 ██████████──────────  50% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 06/10 ████████████────────  60% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 07/10 ██████████████──────  70% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 08/10 ████████████████────  80% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 09/10 ██████████████████──  90% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 10/10 ████████████████████ 100% ");
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
