<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use LogicException;
use mako\cli\output\helpers\ProgressBar;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class ProgressBarTest extends TestCase
{
	/**
	 *
	 */
	public function testProgressWithZeroItems(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->never();

		$progressBar = new ProgressBar($output, 0);

		$progressBar->draw();
	}

	/**
	 *
	 */
	public function testBasicProgress(): void
	{
		$output = Mockery::mock(Output::class);

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

		$progressBar = new class ($output, 10) extends ProgressBar
		{
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		$progressBar->draw();

		for($i = 0; $i < 10; $i++)
		{
			$progressBar->advance();
		}
	}

	/**
	 *
	 */
	public function testProgressWithCustomWidth(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with("\r00/10 ----------------------------------------   0% ");
		$output->shouldReceive('write')->once()->with("\r01/10 ====------------------------------------  10% ");
		$output->shouldReceive('write')->once()->with("\r02/10 ========--------------------------------  20% ");
		$output->shouldReceive('write')->once()->with("\r03/10 ============----------------------------  30% ");
		$output->shouldReceive('write')->once()->with("\r04/10 ================------------------------  40% ");
		$output->shouldReceive('write')->once()->with("\r05/10 ====================--------------------  50% ");
		$output->shouldReceive('write')->once()->with("\r06/10 ========================----------------  60% ");
		$output->shouldReceive('write')->once()->with("\r07/10 ============================------------  70% ");
		$output->shouldReceive('write')->once()->with("\r08/10 ================================--------  80% ");
		$output->shouldReceive('write')->once()->with("\r09/10 ====================================----  90% ");
		$output->shouldReceive('write')->once()->with("\r10/10 ======================================== 100% ");
		$output->shouldReceive('write')->once()->with(PHP_EOL);

		$progressBar = new class ($output, 10) extends ProgressBar
		{
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		$progressBar->setWidth(40);

		$progressBar->draw();

		for($i = 0; $i < 10; $i++)
		{
			$progressBar->advance();
		}
	}

	/**
	 *
	 */
	public function testProgressWithCustomTemplates(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with("\r00/10 ____________________   0% ");
		$output->shouldReceive('write')->once()->with("\r01/10 ++__________________  10% ");
		$output->shouldReceive('write')->once()->with("\r02/10 ++++________________  20% ");
		$output->shouldReceive('write')->once()->with("\r03/10 ++++++______________  30% ");
		$output->shouldReceive('write')->once()->with("\r04/10 ++++++++____________  40% ");
		$output->shouldReceive('write')->once()->with("\r05/10 ++++++++++__________  50% ");
		$output->shouldReceive('write')->once()->with("\r06/10 ++++++++++++________  60% ");
		$output->shouldReceive('write')->once()->with("\r07/10 ++++++++++++++______  70% ");
		$output->shouldReceive('write')->once()->with("\r08/10 ++++++++++++++++____  80% ");
		$output->shouldReceive('write')->once()->with("\r09/10 ++++++++++++++++++__  90% ");
		$output->shouldReceive('write')->once()->with("\r10/10 ++++++++++++++++++++ 100% ");
		$output->shouldReceive('write')->once()->with(PHP_EOL);

		$progressBar = new class ($output, 10) extends ProgressBar
		{
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		$progressBar->setEmptyTemplate('_');

		$progressBar->setFilledTemplate('+');

		$progressBar->draw();

		for($i = 0; $i < 10; $i++)
		{
			$progressBar->advance();
		}
	}

	/**
	 *
	 */
	public function testProgressWithPrefix(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with("\rProcessing files: 00/10 --------------------   0% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 01/10 ==------------------  10% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 02/10 ====----------------  20% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 03/10 ======--------------  30% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 04/10 ========------------  40% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 05/10 ==========----------  50% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 06/10 ============--------  60% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 07/10 ==============------  70% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 08/10 ================----  80% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 09/10 ==================--  90% ");
		$output->shouldReceive('write')->once()->with("\rProcessing files: 10/10 ==================== 100% ");
		$output->shouldReceive('write')->once()->with(PHP_EOL);

		$progressBar = new class ($output, 10) extends ProgressBar
		{
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		$progressBar->setPrefix('Processing files:');

		$progressBar->draw();

		for($i = 0; $i < 10; $i++)
		{
			$progressBar->advance();
		}
	}

	/**
	 *
	 */
	public function testProgressWith100ItemsAndDefaultMinTimeBetweenRedraw(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->times(102);

		$progressBar = new class ($output, 100) extends ProgressBar
		{
			protected function getMicrotime(): float
			{
				static $time = 0;

				return $time += 1.0; // More than the default minTimeBetweenRedraw value of 0.1 secuonds
			}
		};

		$progressBar->draw();

		for($i = 0; $i < 100; $i++)
		{
			$progressBar->advance();
		}
	}

	/**
	 *
	 */
	public function testProgressTimeBetweenRedraw(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->times(3);

		$progressBar = new ProgressBar($output, 100, 0.5);

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

		$output->shouldReceive('write');

		$progressBar = new class ($output, 100, 1) extends ProgressBar
		{
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

		for($i = 0; $i < 100; $i++)
		{
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

		$output->shouldReceive('write')->once()->with("\r00/10 --------------------   0% ");

		$output->shouldReceive('clearLines')->once()->with(1);

		$progressBar = new ProgressBar($output, 10);

		$progressBar->draw();

		$progressBar->remove();
	}

	/**
	 *
	 */
	public function testRemoveComplete(): void
	{
		$output = Mockery::mock(Output::class);

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

		$output->shouldReceive('clearLines')->once()->with(2);

		$progressBar = new class ($output, 10) extends ProgressBar
		{
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		$progressBar->draw();

		for($i = 0; $i < 10; $i++)
		{
			$progressBar->advance();
		}

		$progressBar->remove();
	}

	/**
	 *
	 */
	public function testProgressPast100Percent(): void
	{
		$this->expectException(LogicException::class);

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->times(12);

		$progressBar = new class ($output, 10) extends ProgressBar
		{
			protected function shouldRedraw(): bool
			{
				return ($this->progress % 1) === 0;
			}
		};

		$progressBar->draw();

		for($i = 0; $i < 11; $i++)
		{
			$progressBar->advance();
		}
	}
}
