<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\chrono\stopwatch;

use mako\chrono\stopwatch\Lap;
use mako\tests\TestCase;

/**
 * @group unit
 */
class LapTest extends TestCase
{
	/**
	 *
	 */
	public function testStart(): void
	{
		$lap = new Lap;

		$this->assertEquals(0.0, $lap->getStartTime());

		$this->assertInstanceOf('mako\chrono\stopwatch\Lap', $lap->start());
	}

	/**
	 *
	 */
	public function testStop(): void
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
	public function testIsRunning(): void
	{
		$lap = new Lap;

		$lap->start();

		$this->assertTrue($lap->isRunning());

		$lap->stop();

		$this->assertFalse($lap->isRunning());
	}
}
