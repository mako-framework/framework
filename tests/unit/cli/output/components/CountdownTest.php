<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\output\components\Countdown;
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
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

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
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor

		$output->shouldReceive('write')->once()->with("\r2     ");
		$output->shouldReceive('write')->once()->with("\r2 .   ");
		$output->shouldReceive('write')->once()->with("\r2 ..  ");
		$output->shouldReceive('write')->once()->with("\r2 ... ");
		$output->shouldReceive('write')->once()->with("\r1     ");
		$output->shouldReceive('write')->once()->with("\r1 .   ");
		$output->shouldReceive('write')->once()->with("\r1 ..  ");
		$output->shouldReceive('write')->once()->with("\r1 ... ");
		$output->shouldReceive('write')->once()->with("\r      \r");

		$countdown = Mockery::mock(Countdown::class, [$output]);

		$countdown->shouldAllowMockingProtectedMethods();

		$countdown->makePartial();

		$countdown->shouldReceive('sleep')->times(8);

		$countdown->draw(2);
	}
}
