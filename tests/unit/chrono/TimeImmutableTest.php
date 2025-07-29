<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\chrono;

use DateTime;
use DateTimeZone;
use mako\chrono\Time;
use mako\chrono\TimeImmutable;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class TimeImmutableTest extends TestCase
{
	/**
	 *
	 */
	public function testImmutable(): void
	{
		$time1 = new TimeImmutable;

		$time2 = $time1->forward(10);

		$this->assertNotSame($time1, $time2);
	}

	/**
	 *
	 */
	public function testCopy(): void
	{
		$time1 = new TimeImmutable;

		$time2 = $time1->copy();

		$time1 = $time1->forward(10);

		$this->assertNotSame($time1->getTimestamp(), $time2->getTimestamp());
	}

	/**
	 *
	 */
	public function testGetImmutable(): void
	{
		$time1 = new TimeImmutable('now', 'Europe/Oslo');

		$time1 = $time1->setTimestamp(0);

		$time2 = $time1->getMutable();

		$this->assertInstanceOf(Time::class, $time2);

		$this->assertSame($time1->getTimestamp(), $time2->getTimestamp());

		$this->assertSame($time1->getTimezone()->getName(), $time2->getTimezone()->getName());

		$this->assertSame($time2, $time2->setTimestamp(1));
	}

	/**
	 *
	 */
	public function testConstructor(): void
	{
		$time = new TimeImmutable;

		$this->assertSame($time->format('Y-m-d H:i'), (new DateTime)->format('Y-m-d H:i'));

		//

		$time = new TimeImmutable('yesterday');

		$this->assertSame($time->format('Y-m-d H:i'), (new DateTime('yesterday'))->format('Y-m-d H:i'));

		//

		$time = new TimeImmutable('now', 'Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());

		//

		$time = new TimeImmutable('now', new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());
	}

	/**
	 *
	 */
	public function testNow(): void
	{
		$time = TimeImmutable::now();

		$this->assertSame($time->format('Y-m-d H:i'), (new DateTime)->format('Y-m-d H:i'));

		//

		$time = TimeImmutable::now('Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());

		//

		$time = TimeImmutable::now(new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());
	}

	/**
	 *
	 */
	public function testCreateFromDate(): void
	{
		$time = TimeImmutable::createFromDate(1983);

		$this->assertSame('1983', $time->format('Y'));

		$this->assertSame($time->format('m-d H:i'), (new DateTime)->format('m-d H:i'));

		//

		$time = TimeImmutable::createFromDate(1983, 8);

		$this->assertSame('1983-08', $time->format('Y-m'));

		$this->assertSame($time->format('d H:i'), (new DateTime)->format('d H:i'));

		//

		$time = TimeImmutable::createFromDate(1983, 8, 30);

		$this->assertSame('1983-08-30', $time->format('Y-m-d'));

		$this->assertSame($time->format('H:i'), (new DateTime)->format('H:i'));

		//

		$time = TimeImmutable::createFromDate(1983, 8, 30, 'Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());

		//

		$time = TimeImmutable::createFromDate(1983, 8, 30, new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());
	}

	/**
	 *
	 */
	public function testCreateFromTimestamp(): void
	{
		$time = TimeImmutable::createFromTimestamp(431093532);

		$this->assertSame(431093532, $time->getTimestamp());

		//

		$time = Time::createFromTimestamp(431093532.123);

		$this->assertSame('431093532.123000', $time->format('U.u'));

		//

		$time = TimeImmutable::createFromTimestamp(431093532, 'Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());

		//

		$time = TimeImmutable::createFromTimestamp(431093532, new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());
	}

	/**
	 *
	 */
	public function testCreateFromDOSTimestamp(): void
	{
		$time = TimeImmutable::createFromDOSTimestamp(119431558);

		$this->assertSame('1983-08-30', $time->format('Y-m-d'));
	}

	/**
	 *
	 */
	public function testCreateFromFormat(): void
	{
		$time = TimeImmutable::createFromFormat('Y-m-d H:i:s', '1983-08-30 13:37:33');

		$this->assertSame('1983-08-30 13:37:33', $time->format('Y-m-d H:i:s'));

		//

		$time = TimeImmutable::createFromFormat('Y-m-d H:i:s', '1983-08-30 13:37:33', 'Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());

		//

		$time = TimeImmutable::createFromFormat('Y-m-d H:i:s', '1983-08-30 13:37:33', new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());
	}

	/**
	 *
	 */
	public function testSetTimeZone(): void
	{
		$time = new TimeImmutable;

		$this->assertSame('Asia/Tokyo', $time->setTimeZone('Asia/Tokyo')->getTimeZone()->getName());
	}

	/**
	 *
	 */
	public function testForward(): void
	{
		$time = TimeImmutable::createFromTimestamp(431093532);

		$this->assertSame(431093562, $time->forward(30)->getTimestamp());
	}

	/**
	 *
	 */
	public function testRewind(): void
	{
		$time = TimeImmutable::createFromTimestamp(431093532);

		$this->assertSame(431093502, $time->rewind(30)->getTimestamp());
	}

	/**
	 *
	 */
	public function testGetDOSTimestamp(): void
	{
		$time = TimeImmutable::createFromTimestamp(431093532);

		$this->assertSame(119431558, $time->getDOSTimestamp());
	}

	/**
	 *
	 */
	public function testIsLeapYear(): void
	{
		$time = TimeImmutable::createFromDate(1983);

		$this->assertFalse($time->isLeapYear());

		//

		$time = TimeImmutable::createFromDate(1984);

		$this->assertTrue($time->isLeapYear());
	}

	/**
	 *
	 */
	public function testDaysInMonth(): void
	{
		$time = TimeImmutable::createFromDate(1983, 1);

		$this->assertSame(31, $time->daysInMonth());

		//

		$time = TimeImmutable::createFromDate(1983, 2);

		$this->assertSame(28, $time->daysInMonth());

		//

		$time = TimeImmutable::createFromDate(1984, 2);

		$this->assertSame(29, $time->daysInMonth());

		//

		$time = TimeImmutable::createFromDate(1983, 3);

		$this->assertSame(31, $time->daysInMonth());

		//

		$time = TimeImmutable::createFromDate(1983, 4);

		$this->assertSame(30, $time->daysInMonth());

		//

		$time = TimeImmutable::createFromDate(1983, 5);

		$this->assertSame(31, $time->daysInMonth());

		//

		$time = TimeImmutable::createFromDate(1983, 6);

		$this->assertSame(30, $time->daysInMonth());

		//

		$time = TimeImmutable::createFromDate(1983, 7);

		$this->assertSame(31, $time->daysInMonth());

		//

		$time = TimeImmutable::createFromDate(1983, 8);

		$this->assertSame(31, $time->daysInMonth());

		//

		$time = TimeImmutable::createFromDate(1983, 9);

		$this->assertSame(30, $time->daysInMonth());

		//

		$time = TimeImmutable::createFromDate(1983, 10);

		$this->assertSame(31, $time->daysInMonth());

		//

		$time = TimeImmutable::createFromDate(1983, 11);

		$this->assertSame(30, $time->daysInMonth());

		//

		$time = TimeImmutable::createFromDate(1983, 12);

		$this->assertSame(31, $time->daysInMonth());
	}
}
