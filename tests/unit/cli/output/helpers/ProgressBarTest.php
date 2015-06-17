<?php

namespace mako\tests\unit\cli\output\helpers;

use mako\cli\output\helpers\ProgressBar;

use Mockery as m;

use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */

class ProgressBarTest extends PHPUnit_Framework_TestCase
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

	public function testProgressWithZeroItems()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->never();

		$progressBar = new ProgressBar($output, 0);

		$progressBar->draw();
	}

	/**
	 *
	 */

	public function testBasicProgress()
	{
		$output = m::mock('mako\cli\output\Output');

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

		$progressBar = new ProgressBar($output, 10);

		$progressBar->draw();

		for($i = 0; $i < 10; $i++)
		{
			$progressBar->advance();
		}
	}

	/**
	 *
	 */

	public function testProgressWithCustomTemplates()
	{
		$output = m::mock('mako\cli\output\Output');

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

		$progressBar = new ProgressBar($output, 10);

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

	public function testProgressWith100ItemsAndDefaultRedrawRate()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->times(102);

		$progressBar = new ProgressBar($output, 100);

		$progressBar->draw();

		for($i = 0; $i < 100; $i++)
		{
			$progressBar->advance();
		}
	}

	/**
	 *
	 */

	public function testProgressWith1000ItemsAndDefaultRedrawRate()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->times(102);

		$progressBar = new ProgressBar($output, 1000);

		$progressBar->draw();

		for($i = 0; $i < 1000; $i++)
		{
			$progressBar->advance();
		}
	}

	/**
	 *
	 */

	public function testProgressWith1000ItemsAndCustomtRedrawRate()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->times(1002);

		$progressBar = new ProgressBar($output, 1000, 1);

		$progressBar->draw();

		for($i = 0; $i < 1000; $i++)
		{
			$progressBar->advance();
		}
	}
}