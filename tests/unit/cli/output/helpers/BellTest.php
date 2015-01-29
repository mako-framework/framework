<?php

namespace mako\tests\unit\cli\output\helpers;

use mako\cli\output\helpers\Bell;

use Mockery as m;

/**
 * @group unit
 */

class BellTest extends \PHPUnit_Framework_TestCase
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

	public function testRing()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with("\x07");

		$bell = new Bell($output);

		$bell->ring();
	}

	/**
	 *
	 */

	public function testRingMultipleTimes()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with("\x07\x07\x07");

		$bell = new Bell($output);

		$bell->ring(3);
	}
}