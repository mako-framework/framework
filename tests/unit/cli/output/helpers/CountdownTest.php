<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use mako\cli\output\helpers\Countdown;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class CountdownTest extends TestCase
{
	/**
	 *
	 */
	public function testCountdownFromDefault(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();

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

		/** @var \mako\cli\output\helpers\Countdown|\Mockery\MockInterface $countdown */
		$countdown = Mockery::mock(Countdown::class, [$output]);

		$countdown->shouldAllowMockingProtectedMethods();

		$countdown->makePartial();

		$countdown->shouldReceive('sleep')->times(20);

		$countdown->draw();
	}

	/**
	 *
	 */
	public function testCountdownFrom2(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();

		$output->shouldReceive('write')->once()->with("\r2     ");
		$output->shouldReceive('write')->once()->with("\r2 .   ");
		$output->shouldReceive('write')->once()->with("\r2 ..  ");
		$output->shouldReceive('write')->once()->with("\r2 ... ");
		$output->shouldReceive('write')->once()->with("\r1     ");
		$output->shouldReceive('write')->once()->with("\r1 .   ");
		$output->shouldReceive('write')->once()->with("\r1 ..  ");
		$output->shouldReceive('write')->once()->with("\r1 ... ");
		$output->shouldReceive('write')->once()->with("\r      \r");

		/** @var \mako\cli\output\helpers\Countdown|\Mockery\MockInterface $countdown */
		$countdown = Mockery::mock(Countdown::class, [$output]);

		$countdown->shouldAllowMockingProtectedMethods();

		$countdown->makePartial();

		$countdown->shouldReceive('sleep')->times(8);

		$countdown->draw(2);
	}
}
