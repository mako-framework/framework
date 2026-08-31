<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\gd;

use mako\pixel\image\Color;
use mako\pixel\image\Gd;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\inspectors\gd\ColorAt;
use mako\pixel\image\operations\gd\Watermark;
use mako\pixel\image\operations\WatermarkPosition;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('gd')]
class WatermarkTest extends TestCase
{
	/**
	 * Returns [position, expected red pixel, pixel that should remain black].
	 *
	 * The container is 8x8 and the watermark is 2x2.
	 */
	public static function positionProvider(): array
	{
		return [
			'TopLeft'     => [WatermarkPosition::TopLeft, new Point(0, 0), new Point(2, 2)],
			'Top'         => [WatermarkPosition::Top, new Point(3, 0), new Point(3, 2)],
			'TopRight'    => [WatermarkPosition::TopRight, new Point(7, 0), new Point(5, 2)],
			'CenterLeft'  => [WatermarkPosition::CenterLeft, new Point(0, 3), new Point(2, 3)],
			'Center'      => [WatermarkPosition::Center, new Point(3, 3), new Point(0, 0)],
			'CenterRight' => [WatermarkPosition::CenterRight, new Point(7, 3), new Point(5, 3)],
			'BottomLeft'  => [WatermarkPosition::BottomLeft, new Point(0, 7), new Point(2, 5)],
			'Bottom'      => [WatermarkPosition::Bottom, new Point(3, 7), new Point(3, 5)],
			'BottomRight' => [WatermarkPosition::BottomRight, new Point(7, 7), new Point(5, 5)],
		];
	}

	/**
	 *
	 */
	#[DataProvider('positionProvider')]
	public function testWatermarkPosition(WatermarkPosition $position, Point $red, Point $black): void
	{
		$image = Gd::create(new Dimensions(8, 8), new Color(0, 0, 0));

		$watermark = Gd::create(new Dimensions(2, 2), new Color(255, 0, 0));

		$image->apply(new Watermark($watermark, $position));

		$this->assertSame('#FF0000', $image->inspect(new ColorAt($red))->toHexString());

		$this->assertSame('#000000', $image->inspect(new ColorAt($black))->toHexString());
	}

	/**
	 *
	 */
	public function testWatermarkWithMargin(): void
	{
		$image = Gd::create(new Dimensions(8, 8), new Color(0, 0, 0));

		$watermark = Gd::create(new Dimensions(2, 2), new Color(255, 0, 0));

		$image->apply(new Watermark($watermark, WatermarkPosition::TopLeft, margin: 2));

		// The watermark should span (2, 2)-(3, 3)

		$this->assertSame('#000000', $image->inspect(new ColorAt(new Point(1, 1)))->toHexString());
		$this->assertSame('#FF0000', $image->inspect(new ColorAt(new Point(2, 2)))->toHexString());
		$this->assertSame('#FF0000', $image->inspect(new ColorAt(new Point(3, 3)))->toHexString());
		$this->assertSame('#000000', $image->inspect(new ColorAt(new Point(4, 4)))->toHexString());
	}
}
