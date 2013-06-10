<?php

use \mako\Num;

class NumTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testArabic2roman()
	{
		$this->assertEquals('VII', Num::arabic2roman(7));
		$this->assertEquals('MMMCMXCIX', Num::arabic2roman(3999));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */

	public function testArabic2romanException()
	{
		Num::arabic2roman(9999);
	}

	/**
	 *
	 */

	public function testRoman2arabic()
	{
		$this->assertEquals(7, Num::roman2arabic('VII'));
		$this->assertEquals(3999, Num::roman2arabic('MMMCMXCIX'));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */

	public function testRoman2arabicException()
	{
		Num::roman2arabic('XXXXXXXXXX');
	}
}