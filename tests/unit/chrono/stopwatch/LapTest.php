<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

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

		$this->assertEquals(0.0, $lap->getStartTime());

		$this->assertInstanceOf('mako\chrono\stopwatch\Lap', $lap->start());
	}

	/**
	 *
	 */
	public function testStop()
	{
		$lap = new Lap;

		$lap->start();

		$this->assertEquals(0.0, $lap->getStopTime());

		$lap->stop();

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