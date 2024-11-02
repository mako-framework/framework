<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\output\components\Spinner;
use mako\cli\output\Cursor;
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
		/** @var Cursor|\Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('hide')->once();
		$cursor->shouldReceive('restore')->once();
		$cursor->shouldReceive('clearLine')->once();

		/** @var \Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getCursor')->times(3)->andReturn($cursor);
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
