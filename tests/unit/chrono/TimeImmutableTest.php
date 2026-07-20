<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\chrono;

use DateTimeImmutable;
use DateTimeZone;
use mako\chrono\exceptions\ChronoException;
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
	public function testToImmutable(): void
	{
		$time1 = new TimeImmutable('now', 'Europe/Oslo');

		$time1 = $time1->setTimestamp(0);

		$time2 = $time1->toMutable();

		$this->assertInstanceOf(Time::class, $time2);

		$this->assertSame($time1->getTimestamp(), $time2->getTimestamp());

		$this->assertSame($time1->getTimezone()->getName(), $time2->getTimezone()->getName());

		$this->assertSame($time2, $time2->setTimestamp(1));
	}

	/**
	 *
	 */
	public function testToNative(): void
	{
		$time1 = new TimeImmutable('now', 'Europe/Oslo');

		$time2 = $time1->toNative();

		$this->assertInstanceOf(DateTimeImmutable::class, $time2);
		$this->assertNotInstanceOf(TimeImmutable::class, $time2);

		$this->assertSame($time1->getTimestamp(), $time2->getTimestamp());

		$this->assertSame($time1->getTimezone()->getName(), $time2->getTimezone()->getName());
	}

	/**
	 *
	 */
	public function testConstructor(): void
	{
		$time = new TimeImmutable;

		$this->assertSame($time->format('Y-m-d H:i'), (new DateTimeImmutable)->format('Y-m-d H:i'));

		//

		$time = new TimeImmutable('yesterday');

		$this->assertSame($time->format('Y-m-d H:i'), (new DateTimeImmutable('yesterday'))->format('Y-m-d H:i'));

		//

		$time = new TimeImmutable('now', 'Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimezone()->getName());

		//

		$time = new TimeImmutable('now', new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimezone()->getName());
	}

	/**
	 *
	 */
	public function testNow(): void
	{
		$time = TimeImmutable::now();

		$this->assertSame($time->format('Y-m-d H:i'), (new DateTimeImmutable)->format('Y-m-d H:i'));

		//

		$time = TimeImmutable::now('Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimezone()->getName());

		//

		$time = TimeImmutable::now(new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimezone()->getName());
	}

	/**
	 *
	 */
	public function testCreateFromDate(): void
	{
		$time = TimeImmutable::createFromDate(1983);

		$this->assertSame('1983', $time->format('Y'));

		$this->assertSame((new DateTimeImmutable)->format('m-d 00:00:00'), $time->format('m-d H:i:s'));

		//

		$time = TimeImmutable::createFromDate(1983, 8);

		$this->assertSame('1983-08', $time->format('Y-m'));

		$this->assertSame((new DateTimeImmutable)->format('d 00:00:00'), $time->format('d H:i:s'));

		//

		$time = TimeImmutable::createFromDate(1983, 8, 30);

		$this->assertSame('1983-08-30', $time->format('Y-m-d'));

		$this->assertSame('00:00:00', $time->format('H:i:s'));

		//

		$time = TimeImmutable::createFromDate(1983, 8, 30, 'Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimezone()->getName());

		//

		$time = TimeImmutable::createFromDate(1983, 8, 30, new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimezone()->getName());
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

		$this->assertSame('Asia/Tokyo', $time->getTimezone()->getName());

		//

		$time = TimeImmutable::createFromTimestamp(431093532, new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimezone()->getName());
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

		$this->assertInstanceOf(TimeImmutable::class, $time);
		$this->assertSame('1983-08-30 13:37:33', $time->format('Y-m-d H:i:s'));

		//

		$time = TimeImmutable::createFromFormat('Y-m-d H:i:s.u', '1983-08-30 13:37:33.133700');

		$this->assertInstanceOf(TimeImmutable::class, $time);
		$this->assertSame('1983-08-30 13:37:33.133700', $time->format('Y-m-d H:i:s.u'));

		//

		$time = TimeImmutable::createFromFormat('Y-m-d H:i:s', '1983-08-30 13:37:33', 'Asia/Tokyo');

		$this->assertInstanceOf(TimeImmutable::class, $time);
		$this->assertSame('Asia/Tokyo', $time->getTimezone()->getName());

		//

		$time = TimeImmutable::createFromFormat('Y-m-d H:i:s', '1983-08-30 13:37:33', new DateTimeZone('Asia/Tokyo'));

		$this->assertInstanceOf(TimeImmutable::class, $time);
		$this->assertSame('Asia/Tokyo', $time->getTimezone()->getName());
	}

	/**
	 *
	 */
	public function testCreateFromFormatWithFaiure(): void
	{
		$time = TimeImmutable::createFromFormat('Y-m-d H:i:s', 'fail');

		$this->assertFalse($time);
	}

	/**
	 *
	 */
	public function testCreateFromFormatOrThrow(): void
	{
		$time = TimeImmutable::createFromFormatOrThrow('Y-m-d H:i:s', '1983-08-30 13:37:33');

		$this->assertInstanceOf(TimeImmutable::class, $time);
		$this->assertSame('1983-08-30 13:37:33', $time->format('Y-m-d H:i:s'));
	}

	/**
	 *
	 */
	public function testCreateFromFormatOrThrowWithFaiure(): void
	{
		$this->expectException(ChronoException::class);
		$this->expectExceptionMessageIs('Unable to create mako\chrono\TimeImmutable instance from value [ ass ] for format [ Y-m-d ]. A four digit year could not be found; Not enough data available to satisfy format.');

		TimeImmutable::createFromFormatOrThrow('Y-m-d', 'ass');
	}

	/**
	 *
	 */
	public function testSetTimeZone(): void
	{
		$time = new TimeImmutable;

		$this->assertSame('Asia/Tokyo', $time->setTimezone('Asia/Tokyo')->getTimezone()->getName());
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

	/**
	 *
	 */
	public function testToday(): void
	{
		$now = TimeImmutable::now();
		$today = TimeImmutable::today();

		$this->assertSame($now->format('Y-m-d'), $today->format('Y-m-d'));

		$this->assertSame('00:00:00', $today->format('H:i:s'));
	}

	/**
	 *
	 */
	public function testYesterday(): void
	{
		$today = TimeImmutable::today();
		$yesterday = TimeImmutable::yesterday();

		$this->assertSame($today->modify('-1 day')->format('Y-m-d'), $yesterday->format('Y-m-d'));

		$this->assertSame('00:00:00', $yesterday->format('H:i:s'));
	}

	/**
	 *
	 */
	public function testTomorrow(): void
	{
		$today = TimeImmutable::today();
		$tomorrow = TimeImmutable::tomorrow();

		$this->assertSame($today->modify('+1 day')->format('Y-m-d'), $tomorrow->format('Y-m-d'));

		$this->assertSame('00:00:00', $tomorrow->format('H:i:s'));
	}

	/**
	 *
	 */
	public function testIsPast(): void
	{
		$time = TimeImmutable::yesterday();

		$this->assertTrue($time->isPast());
	}

	/**
	 *
	 */
	public function testIsFuture(): void
	{
		$time = TimeImmutable::tomorrow();

		$this->assertTrue($time->isFuture());
	}

	/**
	 *
	 */
	public function testIsBefore(): void
	{
		$time = TimeImmutable::now();

		$this->assertTrue($time->isBefore(new DateTimeImmutable('+10 hours')));
		$this->assertFalse($time->isBefore(new DateTimeImmutable('-10 hours')));
	}

	/**
	 *
	 */
	public function testIsAfter(): void
	{
		$time = TimeImmutable::now();

		$this->assertTrue($time->isAfter(new DateTimeImmutable('-10 hours')));
		$this->assertFalse($time->isAfter(new DateTimeImmutable('+10 hours')));
	}

	/**
	 *
	 */
	public function testIsBetween(): void
	{
		$time = TimeImmutable::now();

		$this->assertTrue($time->isBetween(new DateTimeImmutable('-10 hours'), new DateTimeImmutable('+10 hours')));

		$this->assertFalse($time->isBetween(new DateTimeImmutable('-10 hours'), new DateTimeImmutable('-5 hours')));

		$this->assertFalse($time->isBetween(new DateTimeImmutable('+5 hours'), new DateTimeImmutable('+10 hours')));
	}

	/**
	 *
	 */
	public function testToAtomString(): void
	{
		$time = new TimeImmutable('2000-01-01 00:00:00', new DateTimeZone('UTC'));

		$this->assertSame('2000-01-01T00:00:00+00:00', $time->toAtomString());
	}

	/**
	 *
	 */
	public function testToIso8601String(): void
	{
		$time = new TimeImmutable('2000-01-01 00:00:00', new DateTimeZone('UTC'));

		$this->assertSame('2000-01-01T00:00:00+00:00', $time->toIso8601String());
	}

	/**
	 *
	 */
	public function testToExpandedIso8601String(): void
	{
		$time = new TimeImmutable('2000-01-01 00:00:00', new DateTimeZone('UTC'));

		$this->assertSame('+2000-01-01T00:00:00+00:00', $time->toExpandedIso8601String());
	}

	/**
	 *
	 */
	public function testToRfc7231String(): void
	{
		$time = new TimeImmutable('2000-01-01 00:00:00', new DateTimeZone('UTC'));

		$this->assertSame('Sat, 01 Jan 2000 00:00:00 GMT', $time->toRfc7231String());
	}
}
