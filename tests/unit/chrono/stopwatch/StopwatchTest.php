<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\chrono\stopwatch;

use mako\chrono\stopwatch\Stopwatch;
use mako\tests\TestCase;

/**
 * @group unit
 */
class StopwatchTest extends TestCase
{
	/**
	 *
	 */
	public function testStart(): void
	{
		$stopwatch = new Stopwatch;

		$this->assertInstanceOf('mako\chrono\stopwatch\Stopwatch', $stopwatch->start());

		$this->assertTrue($stopwatch->getLapCount() === 1);
	}

	/**
	 *
	 */
	public function testLap(): void
	{
		$stopwatch = new Stopwatch;

		$stopwatch->start();

		$this->assertTrue($stopwatch->getLaps()[0]->isRunning());

		$stopwatch->lap();

		$this->assertFalse($stopwatch->getLaps()[0]->isRunning());

		$this->assertTrue($stopwatch->getLaps()[1]->isRunning());
	}

	/**
	 *
	 */
	public function testGetLaps(): void
	{
		$stopwatch = new Stopwatch;

		$stopwatch->start();

		$this->assertContainsOnlyInstancesOf('mako\chrono\stopwatch\Lap', $stopwatch->getLaps());

		$this->assertTrue(count($stopwatch->getLaps()) === 1);
	}

	/**
	 *
	 */
	public function testGetLapCount(): void
	{
		$stopwatch = new Stopwatch;

		$stopwatch->start();

		$this->assertEquals(1, $stopwatch->getLapCount());

		$stopwatch->lap();

		$this->assertEquals(2, $stopwatch->getLapCount());
	}

	/**
	 *
	 */
	public function testStop(): void
	{
		$stopwatch = new Stopwatch;

		$stopwatch->start();

		$stopwatch->lap();

		$stopwatch->lap();

		$this->assertInternalType('float', $stopwatch->stop());

		foreach($stopwatch->getLaps() as $lap)
		{
			$this->assertFalse($lap->isRunning());
		}
	}

	/**
	 *
	 */
	public function testGetElapsedTime(): void
	{
		$stopwatch = new Stopwatch;

		$stopwatch->start();

		$this->assertInternalType('float', $stopwatch->getElapsedTime());

		$stopped = $stopwatch->stop();

		$this->assertEquals($stopped, $stopwatch->getElapsedTime());
	}

	/**
	 *
	 */
	public function testIsRunning(): void
	{
		$stopwatch = new Stopwatch;

		$stopwatch->start();

		$this->assertTrue($stopwatch->isRunning());

		$stopwatch->stop();

		$this->assertFalse($stopwatch->isRunning());
	}
}
