<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\output\components\Bell;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class BellTest extends TestCase
{
	/**
	 *
	 */
	public function testRing(): void
	{
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
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with("\x07\x07\x07");

		$bell = new Bell($output);

		$bell->ring(3);
	}
}
