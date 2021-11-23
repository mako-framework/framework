<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility;

use mako\tests\TestCase;
use mako\utility\exceptions\NumException;
use mako\utility\Num;

/**
 * @group unit
 */
class NumTest extends TestCase
{
	/**
	 *
	 */
	public function testArabic2roman(): void
	{
		$this->assertEquals('VII', Num::arabic2roman(7));
		$this->assertEquals('MMMCMXCIX', Num::arabic2roman(3999));
	}

	/**
	 *
	 */
	public function testArabic2romanException(): void
	{
		$this->expectException(NumException::class);

		Num::arabic2roman(9999);
	}

	/**
	 *
	 */
	public function testRoman2arabic(): void
	{
		$this->assertEquals(7, Num::roman2arabic('VII'));
		$this->assertEquals(3999, Num::roman2arabic('MMMCMXCIX'));
	}

	/**
	 *
	 */
	public function testRoman2arabicException(): void
	{
		$this->expectException(NumException::class);

		Num::roman2arabic('XXXXXXXXXX');
	}
}
