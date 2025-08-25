<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\exceptions\CliException;
use mako\cli\output\components\Progress;
use mako\cli\output\components\progress\AsciiTheme;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ProgressTest extends TestCase
{
	/**
	 *
	 */
	public function testProgressWithZeroItems(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('write')->never();

		$progressBar = new Progress($output, 0);

		$progressBar->draw();
	}

	/**
	 *
	 */
	public function testBasicProgress(): void
	{
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

		$progressBar = new class ($output, 10) extends Progress {
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		$progressBar->draw();

		for ($i = 0; $i < 10; $i++) {
			$progressBar->advance();
		}
	}

	/**
	 *
	 */
	public function testProgressWithCustomWidth(): void
	{
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

		$progressBar = new class ($output, 10, width: 40) extends Progress {
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		$progressBar->draw();

		for ($i = 0; $i < 10; $i++) {
			$progressBar->advance();
		}
	}

	/**
	 *
	 */
	public function testProgressWithCustomTemplates(): void
	{
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

		$progressBar = new class ($output, 10, theme: new AsciiTheme) extends Progress {
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		$progressBar->draw();

		for ($i = 0; $i < 10; $i++) {
			$progressBar->advance();
		}
	}

	/**
	 *
	 */
	public function testProgressWithDescription(): void
	{
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

		$progressBar = new class ($output, 10, description: 'Processing files:') extends Progress {
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		$progressBar->draw();

		for ($i = 0; $i < 10; $i++) {
			$progressBar->advance();
		}
	}

	/**
	 *
	 */
	public function testProgressWith100ItemsAndDefaultMinTimeBetweenRedraw(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('write')->times(102);

		$progressBar = new class ($output, 100) extends Progress {
			protected function getMicrotime(): float
			{
				static $time = 0;

				return $time += 1.0; // More than the default minTimeBetweenRedraw value of 0.1 secuonds
			}
		};

		$progressBar->draw();

		for ($i = 0; $i < 100; $i++) {
			$progressBar->advance();
		}
	}

	/**
	 *
	 */
	public function testProgressTimeBetweenRedraw(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('write')->times(3);

		$progressBar = new Progress($output, 100, minTimeBetweenRedraw: 0.5);

		$progressBar->draw();

		$progressBar->advance(); // Should redraw

		$progressBar->advance(); // Should not redraw

		usleep(500000);

		$progressBar->advance(); // Should redraw
	}

	/**
	 *
	 */
	public function testProgressWith100ItemsAndCustomMinTimeBetweenRedraw(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('write');

		$progressBar = new class ($output, 100, minTimeBetweenRedraw: 1) extends Progress {
			public $drawn = 0;

			protected function getMicrotime(): float
			{
				static $time = 0;

				return $time += 0.5; // Half of the default minTimeBetweenRedraw value
			}

			public function draw(): void
			{
				$this->drawn++;

				parent::draw();
			}
		};

		$progressBar->draw();

		for ($i = 0; $i < 100; $i++) {
			$progressBar->advance();
		}

		$this->assertThat($progressBar->drawn, $this->logicalAnd($this->greaterThan(50), $this->lessThan(55)));
	}

	/**
	 *
	 */
	public function testRemoveIncomplete(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('clearLines')->once()->with(1);

		$output->shouldReceive('write')->once()->with("\r00/10 ────────────────────   0.00% ");

		$progressBar = new Progress($output, 10);

		$progressBar->draw();

		$progressBar->remove();
	}

	/**
	 *
	 */
	public function testRemoveComplete(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('clearLines')->once()->with(2);

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

		$progressBar = new class ($output, 10) extends Progress {
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		$progressBar->draw();

		for ($i = 0; $i < 10; $i++) {
			$progressBar->advance();
		}

		$progressBar->remove();
	}

	/**
	 *
	 */
	public function testProgressPast100Percent(): void
	{
		$this->expectException(CliException::class);

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('write')->times(12);

		$progressBar = new class ($output, 10) extends Progress {
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		$progressBar->draw();

		for ($i = 0; $i < 11; $i++) {
			$progressBar->advance();
		}
	}
}
