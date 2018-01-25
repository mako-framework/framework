<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use Mockery;

use mako\cli\output\helpers\Bell;
use mako\tests\TestCase;

/**
 * @group unit
 */
class BellTest extends TestCase
{
	/**
	 *
	 */
	public function testRing()
	{
		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with("\x07");

		$bell = new Bell($output);

		$bell->ring();
	}

	/**
	 *
	 */
	public function testRingMultipleTimes()
	{
		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with("\x07\x07\x07");

		$bell = new Bell($output);

		$bell->ring(3);
	}
}
