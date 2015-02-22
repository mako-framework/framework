<?php

namespace mako\tests\unit\chrono\stopwatch;

use PHPUnit_Framework_TestCase;

use mako\chrono\stopwatch\Lap;

/**
 * @group unit
 */

class LapTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testStart()
	{
		$lap = new Lap;

		$this->assertNull($lap->getStartTime());

		$this->assertInstanceOf('mako\chrono\stopwatch\Lap', $lap->start());

		$this->assertInternalType('float', $lap->getStartTime());
	}

	/**
	 *
	 */

	public function testStop()
	{
		$lap = new Lap;

		$lap->start();

		$this->assertNull($lap->getStopTime());

		$lap->stop();

		$this->assertInternalType('float', $lap->getStopTime());

		$this->assertTrue($lap->getStopTime() > $lap->getStartTime());
	}

	/**
	 *
	 */

	public function testIsRunning()
	{
		$lap = new Lap;

		$lap->start();

		$this->assertTrue($lap->isRunning());

		$lap->stop();

		$this->assertFalse($lap->isRunning());
	}
}