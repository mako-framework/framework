<?php

namespace mako\tests\unit\chrono\stopwatch;

use PHPUnit_Framework_TestCase;

use mako\chrono\stopwatch\Stopwatch;

/**
 * @group unit
 */

class StopwatchTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testStart()
	{
		$stopwatch = new Stopwatch;

		$this->assertInstanceOf('mako\chrono\stopwatch\Stopwatch', $stopwatch->start());

		$this->assertTrue($stopwatch->getLapCount() === 1);
	}

	/**
	 *
	 */

	public function testLap()
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

	public function testGetLaps()
	{
		$stopwatch = new Stopwatch;

		$stopwatch->start();

		$this->assertContainsOnlyInstancesOf('mako\chrono\stopwatch\Lap', $stopwatch->getLaps());

		$this->assertTrue(count($stopwatch->getLaps()) === 1);
	}

	/**
	 *
	 */

	public function testGetLapCount()
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

	public function testStop()
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

	public function testGetElapsedTime()
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

	public function testIsRunning()
	{
		$stopwatch = new Stopwatch;

		$stopwatch->start();

		$this->assertTrue($stopwatch->isRunning());

		$stopwatch->stop();

		$this->assertFalse($stopwatch->isRunning());
	}
}