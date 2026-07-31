<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\gd;

use mako\pixel\image\Gd;
use mako\pixel\image\inspectors\gd\TopColors;
use mako\pixel\image\operations\gd\Bitonal;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class BitonalTest extends TestCase
{
	/**
	 *
	 */
	public function setUp(): void
	{
		if (!extension_loaded('gd')) {
			$this->markTestSkipped('The "gd" extension is not enabled.');
		}
	}

	/**
	 *
	 */
	public function testBitonal(): void
	{
		$image = new Gd(__DIR__ . '/../../fixtures/002.jpg');

		$image->apply(new Bitonal);

		$colors = $image->inspect(new TopColors);

		$this->assertSame(2, count($colors));

		$this->assertSame('#FFFFFF', $colors[0]->toHexString());
		$this->assertSame('#000000', $colors[1]->toHexString());
	}
}
