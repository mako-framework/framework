<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\output\components\Spinner;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class SpinnerTest extends TestCase
{
	/**
	 *
	 */
	public function testStaticSpinner(): void
	{
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('hideCursor')->once();
		$output->shouldReceive('showCursor')->once();
		$output->shouldReceive('restoreCursor'); // Destructor
		$output->shouldReceive('clearLine')->once();

		$output->shouldReceive('write')->once()->with('Doing something...');

		$spinner = new class($output) extends Spinner {
			protected function canFork(): bool
			{
				return false;
			}
		};

		$result = $spinner->spin('Doing something...', fn () => 123);

		$this->assertSame(123, $result);
	}
}
