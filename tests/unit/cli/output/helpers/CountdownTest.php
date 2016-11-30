<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\cli\output\helpers\Countdown;

/**
 * @group unit
 * @group slow
 */
class CountdownTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 *
	 */
	public function testCountdownFromDefault()
	{
		$output = Mockery::mock('mako\cli\output\Output');

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
		$output = Mockery::mock('mako\cli\output\Output');

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
