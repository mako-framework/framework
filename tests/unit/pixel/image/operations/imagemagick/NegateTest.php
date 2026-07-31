<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use mako\pixel\image\ImageMagick;
use mako\pixel\image\inspectors\imagemagick\TopColors;
use mako\pixel\image\operations\imagemagick\Bitonal;
use mako\pixel\image\operations\imagemagick\Negate;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class NegateTest extends TestCase
{
	/**
	 *
	 */
	public function setUp(): void
	{
		if (!extension_loaded('imagick')) {
			$this->markTestSkipped('The "imagick" extension is not enabled.');
		}
	}

	/**
	 *
	 */
	public function testNegate(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/002.jpg');

		$image->apply(new Bitonal);
		$image->apply(new Negate);

		$colors = $image->inspect(new TopColors);

		$this->assertSame(2, count($colors));

		$this->assertSame('#000000', $colors[0]->toHexString());
		$this->assertSame('#FFFFFF', $colors[1]->toHexString());
	}
}
