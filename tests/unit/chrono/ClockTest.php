<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\chrono;

use DateTimeImmutable;
use mako\chrono\Clock;
use mako\chrono\TimeImmutable;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ClockTest extends TestCase
{
	/**
	 *
	 */
	public function testNow(): void
	{
		$now = (new Clock)->now();

		$this->assertInstanceOf(DateTimeImmutable::class, $now);
		$this->assertInstanceOf(TimeImmutable::class, $now);
	}
}
