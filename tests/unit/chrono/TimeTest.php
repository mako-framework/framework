<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\chrono;

use DateTime;
use DateTimeZone;
use mako\chrono\Time;
use mako\tests\TestCase;

/**
 * @group unit
 */
class TimeTest extends TestCase
{
	/**
	 *
	 */
	public function testMutable(): void
	{
		$time1 = new Time;

		$time2 = $time1->forward(10);

		$this->assertSame($time1, $time2);
	}

	/**
	 *
	 */
	public function testGetImmutable(): void
	{
		$time1 = new Time('now', 'Europe/Oslo');

		$time1->setTimestamp(0);

		$time2 = $time1->getImmutable();

		$this->assertSame($time1->getTimestamp(), $time2->getTimestamp());

		$this->assertSame($time1->getTimezone()->getName(), $time2->getTimezone()->getName());

		$this->assertNotSame($time2, $time2->setTimestamp(1));
	}

	/**
	 *
	 */
	public function testConstructor(): void
	{
		$time = new Time;

		$this->assertSame($time->format('Y-m-d H:i'), (new DateTime)->format('Y-m-d H:i'));

		//

		$time = new Time('yesterday');

		$this->assertSame($time->format('Y-m-d H:i'), (new DateTime('yesterday'))->format('Y-m-d H:i'));

		//

		$time = new Time('now', 'Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());

		//

		$time = new Time('now', new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());
	}

	/**
	 *
	 */
	public function testNow(): void
	{
		$time = Time::now();

		$this->assertSame($time->format('Y-m-d H:i'), (new DateTime)->format('Y-m-d H:i'));

		//

		$time = Time::now('Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());

		//

		$time = Time::now(new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());
	}

	/**
	 *
	 */
	public function testCreateFromDate(): void
	{
		$time = Time::createFromDate(1983);

		$this->assertSame('1983', $time->format('Y'));

		$this->assertSame($time->format('m-d H:i'), (new DateTime)->format('m-d H:i'));

		//

		$time = Time::createFromDate(1983, 8);

		$this->assertSame('1983-08', $time->format('Y-m'));

		$this->assertSame($time->format('d H:i'), (new DateTime)->format('d H:i'));

		//

		$time = Time::createFromDate(1983, 8, 30);

		$this->assertSame('1983-08-30', $time->format('Y-m-d'));

		$this->assertSame($time->format('H:i'), (new DateTime)->format('H:i'));

		//

		$time = Time::createFromDate(1983, 8, 30, 'Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());

		//

		$time = Time::createFromDate(1983, 8, 30, new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());
	}

	/**
	 *
	 */
	public function testCreateFromTimestamp(): void
	{
		$time = Time::createFromTimestamp(431093532);

		$this->assertSame(431093532, $time->getTimestamp());

		//

		$time = Time::createFromTimestamp(431093532, 'Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());

		//

		$time = Time::createFromTimestamp(431093532, new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());
	}

	/**
	 *
	 */
	public function testCreateFromDOSTimestamp(): void
	{
		$time = Time::createFromDOSTimestamp(119431558);

		$this->assertSame('1983-08-30', $time->format('Y-m-d'));
	}

	/**
	 *
	 */
	public function testCreateFromFormat(): void
	{
		$time = Time::createFromFormat('Y-m-d H:i:s', '1983-08-30 13:37:33');

		$this->assertSame('1983-08-30 13:37:33', $time->format('Y-m-d H:i:s'));

		//

		$time = Time::createFromFormat('Y-m-d H:i:s', '1983-08-30 13:37:33', 'Asia/Tokyo');

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());

		//

		$time = Time::createFromFormat('Y-m-d H:i:s', '1983-08-30 13:37:33', new DateTimeZone('Asia/Tokyo'));

		$this->assertSame('Asia/Tokyo', $time->getTimeZone()->getName());
	}

	/**
	 *
	 */
	public function testSetTimeZone(): void
	{
		$time = new Time;

		$this->assertSame('Asia/Tokyo', $time->setTimeZone('Asia/Tokyo')->getTimeZone()->getName());
	}

	/**
	 *
	 */
	public function testForward(): void
	{
		$time = Time::createFromTimestamp(431093532);

		$this->assertSame(431093562, $time->forward(30)->getTimestamp());
	}

	/**
	 *
	 */
	public function testRewind(): void
	{
		$time = Time::createFromTimestamp(431093532);

		$this->assertSame(431093502, $time->rewind(30)->getTimestamp());
	}

	/**
	 *
	 */
	public function testGetDOSTimestamp(): void
	{
		$time = Time::createFromTimestamp(431093532);

		$this->assertSame(119431558, $time->getDOSTimestamp());
	}

	/**
	 *
	 */
	public function testIsLeapYear(): void
	{
		$time = Time::createFromDate(1983);

		$this->assertFalse($time->isLeapYear());

		//

		$time = Time::createFromDate(1984);

		$this->assertTrue($time->isLeapYear());
	}

	/**
	 *
	 */
	public function testDaysInMonth(): void
	{
		$time = Time::createFromDate(1983, 1);

		$this->assertSame(31, $time->daysInMonth());

		//

		$time = Time::createFromDate(1983, 2);

		$this->assertSame(28, $time->daysInMonth());

		//

		$time = Time::createFromDate(1984, 2);

		$this->assertSame(29, $time->daysInMonth());

		//

		$time = Time::createFromDate(1983, 3);

		$this->assertSame(31, $time->daysInMonth());

		//

		$time = Time::createFromDate(1983, 4);

		$this->assertSame(30, $time->daysInMonth());

		//

		$time = Time::createFromDate(1983, 5);

		$this->assertSame(31, $time->daysInMonth());

		//

		$time = Time::createFromDate(1983, 6);

		$this->assertSame(30, $time->daysInMonth());

		//

		$time = Time::createFromDate(1983, 7);

		$this->assertSame(31, $time->daysInMonth());

		//

		$time = Time::createFromDate(1983, 8);

		$this->assertSame(31, $time->daysInMonth());

		//

		$time = Time::createFromDate(1983, 9);

		$this->assertSame(30, $time->daysInMonth());

		//

		$time = Time::createFromDate(1983, 10);

		$this->assertSame(31, $time->daysInMonth());

		//

		$time = Time::createFromDate(1983, 11);

		$this->assertSame(30, $time->daysInMonth());

		//

		$time = Time::createFromDate(1983, 12);

		$this->assertSame(31, $time->daysInMonth());
	}

	/**
	 *
	 */
	public function testFormatLocalized(): void
	{
		$time = Time::createFromDate(1983, 8, 30);

		$locale = setlocale(LC_TIME, null);

		if(setlocale(LC_TIME, ['ja_JP.UTF-8', 'ja_JP.utf8']) !== false)
		{
			$this->assertSame('8月', $time->formatLocalized('%B'));
		}

		if(setlocale(LC_TIME, ['en_US.UTF-8', 'en_US.utf8']) !== false)
		{
			$this->assertSame('August', $time->formatLocalized('%B'));
		}

		setlocale(LC_TIME, $locale);
	}
}
