<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\cli\output\helpers\Bell;

/**
 * @group unit
 */
class BellTest extends PHPUnit_Framework_TestCase
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
