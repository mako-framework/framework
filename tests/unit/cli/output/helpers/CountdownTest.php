<?php

namespace mako\tests\unit\cli\output\helpers;

use mako\cli\output\helpers\Countdown;

use Mockery as m;

/**
 * @group unit
 * @group slow
 */

class CountdownTest extends \PHPUnit_Framework_TestCase
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

	public function testCountdownFromDefault()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with("\r5     ");
		$output->shouldReceive('write')->once()->with("\r5 .   ");
		$output->shouldReceive('write')->once()->with("\r5 ..  ");
		$output->shouldReceive('write')->once()->with("\r5 ... ");
		$output->shouldReceive('write')->once()->with("\r4     ");
		$output->shouldReceive('write')->once()->with("\r4 .   ");
		$output->shouldReceive('write')->once()->with("\r4 ..  ");
		$output->shouldReceive('write')->once()->with("\r4 ... ");
		$output->shouldReceive('write')->once()->with("\r3     ");
		$output->shouldReceive('write')->once()->with("\r3 .   ");
		$output->shouldReceive('write')->once()->with("\r3 ..  ");
		$output->shouldReceive('write')->once()->with("\r3 ... ");
		$output->shouldReceive('write')->once()->with("\r2     ");
		$output->shouldReceive('write')->once()->with("\r2 .   ");
		$output->shouldReceive('write')->once()->with("\r2 ..  ");
		$output->shouldReceive('write')->once()->with("\r2 ... ");
		$output->shouldReceive('write')->once()->with("\r1     ");
		$output->shouldReceive('write')->once()->with("\r1 .   ");
		$output->shouldReceive('write')->once()->with("\r1 ..  ");
		$output->shouldReceive('write')->once()->with("\r1 ... ");
		$output->shouldReceive('write')->once()->with("\r      \r");

		$bell = new Countdown($output);

		$bell->draw();
	}

	/**
	 *
	 */

	public function testCountdownFrom2()
	{
		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with("\r2     ");
		$output->shouldReceive('write')->once()->with("\r2 .   ");
		$output->shouldReceive('write')->once()->with("\r2 ..  ");
		$output->shouldReceive('write')->once()->with("\r2 ... ");
		$output->shouldReceive('write')->once()->with("\r1     ");
		$output->shouldReceive('write')->once()->with("\r1 .   ");
		$output->shouldReceive('write')->once()->with("\r1 ..  ");
		$output->shouldReceive('write')->once()->with("\r1 ... ");
		$output->shouldReceive('write')->once()->with("\r      \r");

		$bell = new Countdown($output);

		$bell->draw(2);
	}
}