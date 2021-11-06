<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use mako\cli\output\helpers\Bell;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class BellTest extends TestCase
{
	/**
	 *
	 */
	public function testRing(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with("\x07");

		$bell = new Bell($output);

		$bell->ring();
	}

	/**
	 *
	 */
	public function testRingMultipleTimes(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with("\x07\x07\x07");

		$bell = new Bell($output);

		$bell->ring(3);
	}
}
