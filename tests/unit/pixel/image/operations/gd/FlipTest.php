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
use mako\pixel\image\operations\FlipDirection;
use mako\pixel\image\operations\gd\Flip;
use mako\pixel\image\operations\gd\Pixel;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('gd')]
class FlipTest extends TestCase
{
	/**
	 * Creates a 2x2 image with the following pixels:
	 *
	 * R G
	 * B W
	 */
	protected function createImage(): Gd
	{
		$image = Gd::create(new Dimensions(2, 2), new Color(255, 0, 0));

		$image->apply(new Pixel(new Point(1, 0), new Color(0, 255, 0)));
		$image->apply(new Pixel(new Point(0, 1), new Color(0, 0, 255)));
		$image->apply(new Pixel(new Point(1, 1), new Color(255, 255, 255)));

		return $image;
	}

	/**
	 * Returns [direction, topLeft, topRight, bottomLeft, bottomRight].
	 */
	public static function flipProvider(): array
	{
		return [
			// R G      G R
			// B W  ->  W B
			'Horizontal' => [FlipDirection::Horizontal, '#00FF00', '#FF0000', '#FFFFFF', '#0000FF'],

			// R G      B W
			// B W  ->  R G
			'Vertical'   => [FlipDirection::Vertical, '#0000FF', '#FFFFFF', '#FF0000', '#00FF00'],
		];
	}

	/**
	 *
	 */
	#[DataProvider('flipProvider')]
	public function testFlip(FlipDirection $direction, string $topLeft, string $topRight, string $bottomLeft, string $bottomRight): void
	{
		$image = $this->createImage();

		$image->apply(new Flip($direction));

		$this->assertSame($topLeft, $image->inspect(new ColorAt(new Point(0, 0)))->toHexString());
		$this->assertSame($topRight, $image->inspect(new ColorAt(new Point(1, 0)))->toHexString());
		$this->assertSame($bottomLeft, $image->inspect(new ColorAt(new Point(0, 1)))->toHexString());
		$this->assertSame($bottomRight, $image->inspect(new ColorAt(new Point(1, 1)))->toHexString());
	}
}
