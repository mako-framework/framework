<?php

namespace mako\tests\unit\utility;

use \DateTime;

use mako\utility\Humanizer;

use \Mockery as m;

/**
 * @group unit
 */

class HumanizerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function getI18n()
	{
		$i18n = m::mock('\mako\i18n\I18n');

		$i18n->shouldReceive('get')->andReturnUsing(function($key, $params = [])
		{
			if(!empty($params))
			{
				return [$key, $params];
			}

			return $key;
		});

		return $i18n;
	}

	/**
	 *
	 */

	public function getHumanizer()
	{
		return new Humanizer($this->getI18n());
	}

	/**
	 *
	 */

	public function testFileSizeBinary()
	{
		$humanizer = $this->getHumanizer();

		$this->assertEquals('0 byte', $humanizer->fileSize(0));

		//

		$this->assertEquals('1 byte', $humanizer->fileSize(1));

		$this->assertEquals('10 byte', $humanizer->fileSize(1 * 10));

		$this->assertEquals('100 byte', $humanizer->fileSize(1 * 100));

		$this->assertEquals('1000 byte', $humanizer->fileSize(1 * 1000));

		//

		$this->assertEquals('1 KiB', $humanizer->fileSize(1024));

		$this->assertEquals('1.5 KiB', $humanizer->fileSize(1024 * 1.5));

		$this->assertEquals('10 KiB', $humanizer->fileSize(1024 * 10));

		$this->assertEquals('100 KiB', $humanizer->fileSize(1024 * 100));

		$this->assertEquals('1000 KiB', $humanizer->fileSize(1024 * 1000));

		//

		$this->assertEquals('1 MiB', $humanizer->fileSize(1024 * 1024));

		$this->assertEquals('1.5 MiB', $humanizer->fileSize(1024 * 1024 * 1.5));

		$this->assertEquals('10 MiB', $humanizer->fileSize(1024 * 1024 * 10));

		$this->assertEquals('100 MiB', $humanizer->fileSize(1024 * 1024 * 100));

		$this->assertEquals('1000 MiB', $humanizer->fileSize(1024 * 1024 * 1000));

		//

		$this->assertEquals('1 GiB', $humanizer->fileSize(1024 * 1024 * 1024));

		$this->assertEquals('1.5 GiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1.5));

		$this->assertEquals('10 GiB', $humanizer->fileSize(1024 * 1024 * 1024 * 10));

		$this->assertEquals('100 GiB', $humanizer->fileSize(1024 * 1024 * 1024 * 100));

		$this->assertEquals('1000 GiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1000));

		//

		$this->assertEquals('1 TiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024));

		$this->assertEquals('1.5 TiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1.5));

		$this->assertEquals('10 TiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 10));

		$this->assertEquals('100 TiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 100));

		$this->assertEquals('1000 TiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1000));

		//

		$this->assertEquals('1 PiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024));

		$this->assertEquals('1.5 PiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1.5));

		$this->assertEquals('10 PiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 10));

		$this->assertEquals('100 PiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 100));

		$this->assertEquals('1000 PiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1000));

		//

		$this->assertEquals('1 EiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024));

		$this->assertEquals('1.5 EiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1.5));

		$this->assertEquals('10 EiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 10));

		$this->assertEquals('100 EiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 100));

		$this->assertEquals('1000 EiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1000));

		//

		$this->assertEquals('1 ZiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024));

		$this->assertEquals('1.5 ZiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1.5));

		$this->assertEquals('10 ZiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 10));

		$this->assertEquals('100 ZiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 100));

		$this->assertEquals('1000 ZiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1000));

		//

		$this->assertEquals('1 YiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024));

		$this->assertEquals('1.5 YiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1.5));

		$this->assertEquals('10 YiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 10));

		$this->assertEquals('100 YiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 100));

		$this->assertEquals('1000 YiB', $humanizer->fileSize(1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1000));
	}

	/**
	 *
	 */

	public function testFileSizeDecimal()
	{
		$humanizer = $this->getHumanizer();

		$this->assertEquals('0 byte', $humanizer->fileSize(0, false));

		//

		$this->assertEquals('1 byte', $humanizer->fileSize(1, false));

		$this->assertEquals('10 byte', $humanizer->fileSize(1 * 10, false));

		$this->assertEquals('100 byte', $humanizer->fileSize(1 * 100, false));

		//

		$this->assertEquals('1 KB', $humanizer->fileSize(1000, false));

		$this->assertEquals('1.5 KB', $humanizer->fileSize(1000 * 1.5, false));

		$this->assertEquals('10 KB', $humanizer->fileSize(1000 * 10, false));

		$this->assertEquals('100 KB', $humanizer->fileSize(1000 * 100, false));

		//

		$this->assertEquals('1 MB', $humanizer->fileSize(1000 * 1000, false));

		$this->assertEquals('1.5 MB', $humanizer->fileSize(1000 * 1000 * 1.5, false));

		$this->assertEquals('10 MB', $humanizer->fileSize(1000 * 1000 * 10, false));

		$this->assertEquals('100 MB', $humanizer->fileSize(1000 * 1000 * 100, false));

		//

		$this->assertEquals('1 GB', $humanizer->fileSize(1000 * 1000 * 1000, false));

		$this->assertEquals('1.5 GB', $humanizer->fileSize(1000 * 1000 * 1000 * 1.5, false));

		$this->assertEquals('10 GB', $humanizer->fileSize(1000 * 1000 * 1000 * 10, false));

		$this->assertEquals('100 GB', $humanizer->fileSize(1000 * 1000 * 1000 * 100, false));

		//

		$this->assertEquals('1 TB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000, false));

		$this->assertEquals('1.5 TB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1.5, false));

		$this->assertEquals('10 TB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 10, false));

		$this->assertEquals('100 TB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 100, false));

		//

		$this->assertEquals('1 PB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000, false));

		$this->assertEquals('1.5 PB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1.5, false));

		$this->assertEquals('10 PB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 10, false));

		$this->assertEquals('100 PB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 100, false));

		//

		$this->assertEquals('1 EB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1000, false));

		$this->assertEquals('1.5 EB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1.5, false));

		$this->assertEquals('10 EB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 10, false));

		$this->assertEquals('100 EB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 100, false));

		//

		$this->assertEquals('1 ZB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000, false));

		$this->assertEquals('1.5 ZB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1.5, false));

		$this->assertEquals('10 ZB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 10, false));

		$this->assertEquals('100 ZB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 100, false));

		//

		$this->assertEquals('1 YB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000, false));

		$this->assertEquals('1.5 YB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1.5, false));

		$this->assertEquals('10 YB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 10, false));

		$this->assertEquals('100 YB', $humanizer->fileSize(1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 1000 * 100, false));
	}

	/**
	 *
	 */

	public function testDay()
	{
		$humanizer = $this->getHumanizer();

		//

		$dateTime = new DateTime;

		$this->assertEquals('humanizer.today', $humanizer->day($dateTime));

		//

		$dateTime = (new DateTime)->setTimestamp(time() - (60 * 60 * 24));

		$this->assertEquals('humanizer.yesterday', $humanizer->day($dateTime));

		//

		$dateTime = (new DateTime)->setTimestamp(time() + (60 * 60 * 24));

		$this->assertEquals('humanizer.tomorrow', $humanizer->day($dateTime));

		//

		$dateTime = (new DateTime)->setTimestamp(time() + (60 * 60 * 24 * 10));

		$this->assertEquals($dateTime->format('Y-m-d, H:i'), $humanizer->day($dateTime));

		//

		$dateTime = (new DateTime)->setTimestamp(time() + (60 * 60 * 24 * 10));

		$this->assertEquals($dateTime->format('Y/m/d, H:i'), $humanizer->day($dateTime, 'Y/m/d, H:i'));
	}

	/**
	 *
	 */

	public function testTime()
	{
		$humanizer = $this->getHumanizer();

		//

		$dateTime = (new DateTime)->setTimestamp(time() + (60 * 1));

		$this->assertEquals('humanizer.in_minute', $humanizer->time($dateTime));

		//

		$dateTime = (new DateTime)->setTimestamp(time() + (60 * 10));

		$this->assertEquals(['humanizer.in_minutes', [10]], $humanizer->time($dateTime));

		//

		$dateTime = (new DateTime)->setTimestamp(time() - (60 * 1));

		$this->assertEquals('humanizer.minute_ago', $humanizer->time($dateTime));

		//

		$dateTime = (new DateTime)->setTimestamp(time() - (60 * 10));

		$this->assertEquals(['humanizer.minutes_ago', [10]], $humanizer->time($dateTime));

		//

		$dateTime = (new DateTime)->setTimestamp(time() - (60 * 60 * 24 * 10));

		$this->assertEquals($dateTime->format('Y-m-d, H:i'), $humanizer->time($dateTime));

		//

		$dateTime = (new DateTime)->setTimestamp(time() - (60 * 60 * 24 * 10));

		$this->assertEquals($dateTime->format('Y/m/d, H:i'), $humanizer->time($dateTime, 'Y/m/d, H:i'));
	}
}